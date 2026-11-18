# AGENTS.md — constraints

Binding constraints for any agent (human or AI) working in this repo.
This file overrides "it looks cleaner", "it was easier", and "the linter
didn't complain". When a constraint blocks you, stop and record the
conflict in a new ADR under `docs/decision/` — do not deviate silently.

Read this file, then `make context`, before touching anything. Run
`composer install` once: it installs mago, PHPUnit, and the git hook that
gates every commit.

---

## 1. Hard constraints

1. **Docs before code.** No code lands unless it satisfies a line in
   `docs/product/`. If the feature isn't in the product docs, write the
   product doc first (or don't write the code).
2. **`docs/rules/` is binding.** `architecture.md`, `backend.md`,
   `logic.md`. Code that breaks a rule is a rejected PR, not a
   discussion. Breaking a rule requires a new ADR that supersedes it,
   merged before the code.
3. **Scope is fixed: thin adapter.** Map `craft-ai-observer`
   `NormalizedRecord` → Langfuse objects and flush. Nothing else. No
   normalization, no span-tree building, no provider knowledge, no
   second observer, no CMS-observability features.
4. **Dependency allow-list.** `craftcms/cms ^6`, `craft-ai-observer`,
   `langfuse/laravel`. Anything else needs an ADR with a stated
   alternative and a removal plan. Do not vendor, patch, or fork them.
5. **Observability is read-only and non-blocking.** Never mutate agent
   prompts/models/config. Never let a Langfuse error reach the agent
   call: catch, log, swallow. Any change that can add latency to the
   agent path is out, including "just one synchronous call".
6. **Secrets.** Keys come from `.env` → `config()` → plugin settings.
   Never hardcode, never log (not at `debug`), never commit, never put
   in `project.yaml`. Prompts/outputs are logged only by Langfuse
   server-side.
7. **No local state.** No database tables, no migrations, no cache
   tables, no files written at runtime.
8. **No invented facts in docs.** If you cannot verify a behaviour from
   this repo, write `Unverified — needs owner input` or ask. Never
   document a setting, env var, or screen that does not exist.
9. **The toolchain is the gate.** mago is the only linter, formatter and
   analyzer; PHPUnit the only test runner. `make check` green, or it is
   not done. Do not add a second tool, a baseline, or a blanket ignore.
   A rule that can be mechanised must exist as a mago rule plus a
   fixture — see `docs/rules/toolchain.md`.

## 2. How to work with the docs

Two kinds of documents. Treat them differently.

| Kind | Files | Agent action |
|---|---|---|
| **Shared context** | `docs/product/*.md`, `docs/decision/*.md`, `docs/rules/*.md` | Read every session. **Never rewrite.** Append-only: new product doc, new ADR. |
| **Working docs** | `docs/features/*.md`, `docs/prompts/`, `docs/conventions/`, `README.md` | Produce and maintain. |

Rules for shared context:

- **Read order:** `AGENTS.md` → `docs/product/` → `docs/rules/` (all
  four: architecture, backend, logic, toolchain) → the ADRs relevant to
  your change → code.
- **Append, don't edit.** An accepted product doc or rule file stays as
  written. Changed decision ⇒ new ADR in `docs/decision/` with
  `Status: Accepted` that says what it supersedes. Changed scope ⇒ new
  numbered product doc. Changed constraint ⇒ new rule file, never a
  quiet edit of an old one.
- **Never renumber or reuse numbers.** ADRs are `NNNN-slug.md`
  (zero-padded, sequential). Product docs are `NN-slug.md`. Feature
  docs are `NN-slug.md`. Git history is the changelog.
- **Every doc starts with** `Type:`, `Status:`, `Date:` lines. No
  exceptions.
- **One decision per file.** If a doc needs "and also", it is two docs.
- **Relative links only.** `docs/` is export-ignored from release
  archives — nothing may link to it from runtime code.

Rules for working docs:

- **Every shipped feature gets a usage doc** at
  `docs/features/NN-slug.md`, generated from
  `docs/prompts/generate-feature-readme.md` and checked against
  `docs/conventions/craft-plugin-readme.md`. Not optional, not
  "later", not a paragraph buried in another file.
- **Feature docs are usage docs** — how to turn it on, configure it,
  use it, verify it, fix it. Decisions, rationale, and internal design
  belong in `docs/decision/` and `docs/rules/`, not in the feature doc.
- **Root `README.md` stays short** (what it is, install, config,
  feature list with links). It never duplicates a feature doc.
- **No new top-level folders.** New docs go into the existing folders;
  if a folder is genuinely needed, add it to the table above first.

## 3. Definition of done

All boxes checked or the change is not done:

- [ ] Code satisfies a `docs/product/` line; `docs/rules/` read and
      respected (or a superseding ADR merged).
- [ ] `make check` green: docs contract + mago lint/format/analyze +
      rule fixtures + PHPUnit. No suppressions added.
- [ ] A constraint change moved three things together: the rule text,
      the mago rule, and its fixtures (fire + clean).
- [ ] Failure path proven: Langfuse unreachable ⇒ logged, swallowed,
      agent call unaffected.
- [ ] Tests: record mapping (usage, errors, streaming) + the
      "Langfuse down" case.
- [ ] `docs/features/NN-slug.md` written from the prompt, no `{{ }}`
      placeholders, no TODOs left.
- [ ] Root `README.md` feature list links the new doc.
- [ ] No new dependency, no migration, no secret, no synchronous
      network call on the request path.

## 4. Commands

One command per job. Do not memorise paths. `composer install` once
installs mago, PHPUnit, and the pre-commit hook.

| Command | Does |
|---|---|
| `make` / `make help` | List targets. |
| `make context` | Print the shared-context docs (product + all rules + ADR list). Run at session start. |
| `make agents` | Print this file. |
| `make feature FEATURE="<name>"` | Render the feature-README prompt for `<name>` (next number, slug, date, target path filled in). |
| `make features` | List shipped feature docs. |
| `make check` | The gate: docs contract + `composer check` (mago + PHPUnit). Run before every commit. |
| `make qa` | `composer check` only: lint, format:check, analyze, verify-rules, test. |
| `make fix` | Auto-fix what mago can: `lint:fix` + `format`. Then re-run `make check`. |
| `make test` | PHPUnit only. |

## 5. When unsure

1. Grep the repo (`rg`) for the term before assuming it doesn't exist.
2. Prefer the conservative reading of a rule over the convenient one.
3. If two rules conflict, do not pick one — write the ADR that resolves
   it and stop until it is accepted.
4. Ask once, precisely. "Which of A/B?" beats "how should I do this?".
