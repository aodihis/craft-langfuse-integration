# 01 — Forward AI telemetry to Langfuse

**Type:** Product decision
**Status:** Accepted
**Date:** 2026-11-16

## What this plugin is

`craft-langfuse-integration` is a **Craft CMS 6 plugin** that forwards AI
agent telemetry to [Langfuse](https://langfuse.com). It is a **thin
adapter**: all listening and normalization happens in the
`craft-ai-observer` library; this plugin only maps the normalized records
to Langfuse's trace/generation/span schema and sends them.

It is the first of (potentially) several observer plugins:
`craft-langsmith-integration` would follow later, sharing the same library.

```
laravel/ai events ──► craft-ai-observer (library)
                            │  NormalizedRecord
                            ▼
              craft-langfuse-integration (this plugin)
                            │  Langfuse SDK
                            ▼
                        Langfuse
```

## What it must do

1. Register as a Craft 6 plugin that hooks the library's normalized-record
   output.
2. Map records → Langfuse objects (trace, generation, span, tool calls)
   with usage, latency, provider, model, and errors.
3. Read credentials from `.env` / settings:
   `LANGFUSE_PUBLIC_KEY`, `LANGFUSE_SECRET_KEY`, `LANGFUSE_HOST`
   (check: `LANGFUSE_REGION` / self-hosted URL).
4. Flush without slowing the CMS: async/queue or background batch, and
   never fail the agent call when Langfuse is unreachable.
5. Toggle on/off per site or environment.

## What it must NOT do

- Re-implement span-tree logic (library's job).
- Talk to any other observer (LangSmith etc. get their own plugin).
- Change agent prompts, models, or provider config in any way —
  observability is read-only.
- Store or log API keys / prompts beyond what the operator's Langfuse
  project sees.

## Success criteria

- Chat with the CP AI assistant (or any agent task) produces a complete
  Langfuse trace: generations with input/output, token usage, latency,
  tool calls, and errors.
- Failure of Langfuse never affects the CMS request.
- Baseline (observability disabled) changes nothing measurable.