# Rules — Backend (craft-langfuse-integration)

**Status:** Accepted
**Date:** 2026-11-16

## Stack

- Craft CMS 6 (currently `6.0.0-beta.1` in this project), PHP `^8.4`
  (project runs 8.5), DDEV `cms6`.
- Depends: `craftcms/cms ^6.0`, `craft-ai-observer` (path repository during
  development), `langfuse/laravel`.

## Plugin conventions

1. Composer package name `craft-langfuse-integration`; Craft plugin handle
   `langfuse` (confirm against Craft 6's loader; handle must be
   camelCase-friendly) and a unique icon/description.
2. PSR-4 namespace for the plugin source; keep the `docs/` and future
   `tests/` separate from `src/`.
3. Credentials strictly via `.env` → `config()` → plugin settings; nothing
   hardcoded, nothing in `project.yaml`.
4. Logging via Craft's logger at `info`/`warning`; never log prompts
   contents or keys at `debug` in production builds (Langfuse already
   stores them server-side).
5. No database tables/migrations. Queued flush uses Laravel's queue
   (`database` driver is already configured in this project).
6. Tests: mapping unit tests with mock records (including token usage,
   errors, streaming accumulation) + a "Langfuse down" test proving no
   exception reaches the agent call.

## Versioning

- Follow Craft plugin versioning; every release pairs with a tested
  `craft-ai-observer` version (declare the constraint explicitly).