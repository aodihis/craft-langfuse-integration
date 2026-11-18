# Rules — Toolchain (craft-langfuse-integration)

**Status:** Accepted
**Date:** 2026-11-18

Mirrors the `craft-ai-observer` toolchain. Same gate, same custom rules,
same enforcement mechanics — only the adapter-specific rules differ.

## Environment

- PHP `^8.4` (floor), local runtime 8.5. One floor, in four places:
  `composer.json` (`require.php`), `config.platform.php`, `mago.toml`
  (`php-version`), and the rule fixtures' `php-version`. Change all four
  together or none.
- `mago` (`carthage-software/mago`) is the only linter, formatter, and
  static analyzer. No PHP_CodeSniffer, no Pint, no PHPStan, no ECS.
- PHPUnit for tests. No Pest, no custom test harness.

## The gate

```sh
composer check   # lint + format:check + analyze + verify-rules + test
composer format  # writes: normalises CRLF to LF per .gitattributes
make check       # the above plus the docs contract (bin/check-docs.sh)
```

`composer check` green before hand-over. If it fails, the failure is the
author's to fix. `.githooks/pre-commit` runs it on every commit;
`--no-verify` is for emergencies and never hides a red build.

## Code rules

1. `declare(strict_types=1)`, PSR-12, LF line endings, fully typed.
2. **Rules are executable.** Every rule in `docs/rules/` that can be
   expressed mechanically is a mago rule — built-in raised to `error` in
   `mago.toml` or custom in `dev/mago/src/Linter/Rules/`. Changing one
   means changing three things in the same commit: the rule text here,
   the mago rule, and a fixture in `dev/mago/fixtures/` that proves it
   fires (plus a clean fixture proving it does not over-fire).
   `composer verify-rules` fails when fixtures drift.
3. **Custom rules:**

   | Rule | Enforces |
   |---|---|
   | `craft/comment-placement` | A comment is short (≤ 4 prose lines) and sits directly above a function, method, or class. No inline notes, no comments above statements, properties, or constants. Tag-only docblocks (`@var`, `@mago-expect`, …) are allowed anywhere. |
   | `craft/forbidden-imports` | No `CraftAiObserver\Tree\`, `CraftAiObserver\Dev\`, `Laravel\Ai\`, `LangSmith\`, `OpenTelemetry\`, `Psr\Http\`, `GuzzleHttp\`, `Symfony\Component\HttpClient\` references. Only the normalized record crosses into the adapter. |
   | `craft/no-facade-log` | No `Log` facade, import, or call. Inject `Psr\Log\LoggerInterface`. |
   | `craft/no-single-line-control-structure` | No `if ($x) { return; }`, one-line loop bodies, one-line `else`/`elseif`. |
   | `craft/readonly-value-objects` | Classes under `src/Model/` are `readonly`. |

   Built-ins raised to `error`: `no-nested-ternary`, `no-shorthand-ternary`
   (`$a ?: $b`), `block-statement` (unbraced bodies).
   `excessive-parameter-list` keeps the default five for behaviour methods
   and allows twelve for constructors of mapped data payloads.
4. **Do not silence findings.** No baselines, no blanket ignores, no
   `phpcs:ignore`-style comments. A suppression is a last resort and must
   carry the reason in the same docblock (`@mago-expect`).
5. **Do not work around a rule.** Moving a comment to dodge the linter, or
   splitting one unreadable expression into two unreadable ones, is worse
   than deleting the code.
6. **The rule sources are code.** They are type-checked (`mago analyze`),
   formatted, and linted like `src/`. Their fixtures are excluded from the
   normal run and linted only by `dev/mago/verify.php`.

## Runtime dependencies

`composer.json` currently declares the toolchain only. The plugin's runtime
`require` block (`craftcms/cms ^6`, `craftcms/craft-ai-observer`,
`langfuse/laravel`) lands with the scaffold, per
`docs/rules/backend.md`; until then `make check` gates docs + toolchain,
not a plugin that does not exist yet. Adding those requirements is not an
architecture change and needs no ADR.
