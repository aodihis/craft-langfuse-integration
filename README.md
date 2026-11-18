# craft-langfuse-integration

Craft CMS 6 plugin that forwards AI agent telemetry to
[Langfuse](https://langfuse.com). A thin adapter on top of the
[craft-ai-observer](https://github.com/aodihis/craft-ai-observer) library.

## Features

Every shipped feature has a usage doc in [`docs/features/`](docs/features/).
The shape follows [`docs/conventions/craft-plugin-readme.md`](docs/conventions/craft-plugin-readme.md).

_No features documented yet._

## Development

```bash
composer install   # mago + PHPUnit, and the pre-commit hook
make check         # the gate: docs contract + composer check
```

| Command | Does |
|---|---|
| `make` | List targets. |
| `make context` | Print the shared context: product docs, rules, ADR list. Run at session start. |
| `make feature FEATURE="<name>"` | Render the feature-README prompt for `<name>`. |
| `make check` | The gate: docs contract + mago + PHPUnit. |
| `make fix` | Auto-fix lint and format, then re-run `make check`. |

Constraints for contributors and agents: [`AGENTS.md`](AGENTS.md).
Toolchain and code rules: [`docs/rules/toolchain.md`](docs/rules/toolchain.md).

## Docs

- [Product decisions](docs/product/)
- [Engineering decisions (ADR)](docs/decision/)
- [Working rules](docs/rules/)
- [Toolchain rules (mago, PHPUnit)](docs/rules/toolchain.md)
- [Craft plugin README convention](docs/conventions/craft-plugin-readme.md)
- [Feature README prompt](docs/prompts/generate-feature-readme.md)
