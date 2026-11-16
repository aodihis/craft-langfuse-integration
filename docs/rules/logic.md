# Rules — Logic (craft-langfuse-integration)

**Status:** Accepted
**Date:** 2026-11-16

## Mapping: NormalizedRecord → Langfuse

| craft-ai-observer record | Langfuse object(s) |
|---|---|
| `Trace` (id = invocationId) | `CreateTrace` (name: `agent`, input: first user prompt, metadata: provider/model) |
| `Generation` (per step) | `CreateGeneration` (input: step messages) → `UpdateGeneration` (output, usage, latency, model; `statusMessage` on error) |
| `ToolCall` | `CreateSpan` (input: tool + arguments) → `CreateSpan` update / `UpdateSpan` (output/result, error) |
| Trace ends (success) | `FinalizeTrace` / batch update |
| Trace ends (error) | trace level `statusMessage` = exception message |

## Field mapping details

- Usage from `Metrics`: `inputTokens` → `promptTokens`,
  `outputTokens` → `completionTokens`, reasoning/cache tokens → Langfuse
  level input/output metadata if the SDK exposes them (else a
  `metrics` metadata map, keep stable).
- Latency: `time` (ms) from events; Langfuse SDK computes from start/end —
  pass explicit timestamps where supported.
- Provider/model: from the record's `Meta` (prefer the *reported* model in
  `StepCompleted`; fall back to the requested one).
- Sub-agent runs (parent ids set): create the child trace/generation with
  Langfuse's parent linkage (`traceId` + either generation/span as parent)
  so the tree stays intact.

## Behavioral rules

1. **Order**: send `Create*` for a step *after* the open event is fully
   consumed, and `Update*` on completion — never start the next generation
   until the current one is flushed, so Langfuse keeps parent-child order.
2. **Streaming**: deltas accumulate in the library; the adapter only closes
   generations on terminal step events (no per-chunk updates).
3. **Errors**: any Langfuse client exception is caught, logged, and
   swallowed. The agent path must never see it.
4. **Async**: flush via a queued job (database queue) with
   `ShouldBeUnique` keyed by trace id; on retry, prefer idempotent updates.
5. **Sampling** (later): a percentage knob in config; default 100%.
6. **Secrets**: convert `LANGFUSE_PUBLIC_KEY`/`SECRET_KEY` to
   `base64(public:secret)` for the SDK's `Authorization` header; never log
   the token.