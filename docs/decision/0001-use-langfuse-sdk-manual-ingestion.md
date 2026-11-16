# 0001 — Use the official Langfuse SDK, driven manually from normalized records

**Status:** Accepted
**Date:** 2026-11-16

## Context

We forward telemetry to Langfuse. Langfuse offers:

- an official PHP/Laravel package (`langfuse/laravel`), which includes
  auto-instrumentation for the OpenAI/Anthropic **PHP SDKs** and helpers
  for traces/generations/spans;
- a REST API;
- an OTLP ingestion endpoint.

Our LLM calls go through `Laravel\Ai`, which does **not** use the
OpenAI/Anthropic PHP SDKs — it calls the Chat Completions endpoint directly
via `Illuminate\Http\Client`. So Langfuse's auto-instrumentation cannot
attach to our calls (verified: `laravel/ai` v1.4.0, no `openai-php` in the
dependency tree).

## Decision

Use the **official Langfuse PHP/Laravel client** (`langfuse/laravel` or the
underlying `langfuse/sdk-php`) **only as the transport**, and drive
ingestion manually from `craft-ai-observer` normalized records:

- `Prompts/…` → `CreateTrace` / `CreateGeneration` / `CreateSpan` /
  `UpdateGeneration` per the mapping in `docs/rules/logic.md`.
- Adapter owns credentials, env-prefix, host, and release/version metadata.

## Alternatives considered and rejected

- **Auto-instrumentation / vendor middleware**: impossible — laravel/ai
  bypasses the SDKs it hooks.
- **Raw HTTP to the Langfuse API**: re-implements auth/signing, retries,
  batching, and the client's model helpers for no gain.
- **OTLP export**: Langfuse supports it, but requires a full OTel SDK in
  PHP, losing the Langfuse-native generation/usage ergonomics; note it as a
  later option if we add an OTLP observer.

## Consequences

- We maintain the mapping table (normalized record ↔ Langfuse object)
  ourselves; it is small and stable.
- We must handle streaming accumulation before closing a generation
  (library already accumulates deltas; adapter closes on terminal step).
- Version bumps of `langfuse/laravel` may change client APIs; isolated in
  this adapter.