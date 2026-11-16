# Rules — Architecture (craft-langfuse-integration)

**Status:** Accepted
**Date:** 2026-11-16

## Role

A **thin adapter** between `craft-ai-observer` normalized records and
Langfuse. No span-tree logic, no normalization, no provider knowledge.

```
Craft 6 ── laravel/ai events
        └─► craft-ai-observer ── NormalizedRecord
                 └─► [this plugin] LangfuseAdapter (map + flush)
                         └─► langfuse/laravel client ──► Langfuse API
```

## Hard rules

1. All mapping lives in this plugin: `NormalizedRecord` → Langfuse
   trace/generation/span. If two observer plugins ever share mapping code,
   promote it to the library (as a documented, versioned adapter contract),
   not copied.
2. The plugin only depends on: `craftcms/cms ^6`, `craft-ai-observer`,
   `langfuse/laravel`. Nothing else.
3. Flush is **fire-and-forget**: queue job or background flush; the agent
   request must not block on Langfuse. Failure to reach Langfuse is logged,
   not thrown.
4. No data is stored locally (no tables): Langfuse is the store. Check
   whether the queue job needs `ShouldBeUnique` semantics to avoid
   duplicate flushes on retries.
5. Configuration: `LANGFUSE_*` env vars (public key, secret key, host) +
   a settings toggle. Secrets never committed, never logged.
6. The plugin must be harmless when the library is absent or observability
   is disabled (no listeners registered, zero overhead).

## Plugin shape (Craft 6 conventions, refine when scaffolding)

```
craft-langfuse-integration/
  composer.json          # name, type: craft-plugin, handle
  src/
    Plugin.php           # bootstrap, settings, registers adapter
    Adapter/
      LangfuseAdapter.php
      RecordMapper.php   # NormalizedRecord → Langfuse objects
      FlushJob.php       # queued, fire-and-forget
  config/
  resources/
  docs/
```

Craft 6 is still beta (Laravel-based rework); confirm the plugin
scaffolding conventions (namespace, service provider registration vs the
former `craft-plugin` loader) when we scaffold code.