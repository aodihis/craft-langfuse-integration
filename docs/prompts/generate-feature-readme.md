# Prompt — generate a feature README

**Type:** Prompt (working doc)
**Status:** Accepted
**Date:** 2026-11-16
**Render with:** `make feature FEATURE="<feature name>"`

Placeholders (`{{ }}`) are filled by `make`. If any are still present
when you paste this, re-run `make feature` — do not hand-edit the
placeholder text.

---

You are writing the user-facing README for one shipped feature of the
Craft CMS 6 plugin **craft-langfuse-integration**.

- Feature: `{{FEATURE}}`
- Output file: `{{PATH}}` (create it; number `{{NN}}`, date `{{DATE}}`)

## Read first (in this order)

1. `docs/conventions/craft-plugin-readme.md` — the section order, style
   rules, and anti-patterns you must follow. Non-negotiable.
2. `docs/rules/logic.md` and `docs/rules/architecture.md` — to know what
   the plugin is allowed to say and do.
3. The code for the feature (`src/`), plus its tests and config. The
   code is the source of truth for option names, env vars, commands,
   and defaults.
4. The product doc (`docs/product/`) and any ADR (`docs/decision/`)
   that motivated the feature — read for context, never copy into the
   feature doc.

## Constraints

- **Document only what exists.** Every env var, setting key, command,
  event, and CP path must be findable in the repo. If you cannot find
  it, write `Unverified — needs owner input` and list it at the end of
  your reply. Do not guess a plausible name.
- **Usage, not design.** No "because", no alternatives, no trade-offs.
  Those live in `docs/decision/`. The reader wants to use the feature.
- You may read and write only: `{{PATH}}` and `README.md` (feature
  list). Do not edit `docs/product/`, `docs/decision/`,
  `docs/rules/`, or `docs/conventions/`.
- No new files. One feature = one file. If the feature needs two docs,
  it is two features.
- Copy the install section? No — link to the root README's
  Installation section instead.
- No secrets, no real API keys, no real prompt/PII content in examples.

## Steps

1. If the feature's scope, name, or config surface is ambiguous, ask
   at most **three** precise questions before writing. Otherwise write.
2. Write `{{PATH}}` with exactly these sections, in this order, using
   the names verbatim:

   - `# {{NN}} — <feature name>`, header block: `**Type:** Feature usage`,
     `**Status:** Shipped`, `**Date:** {{DATE}}`, `**Since:** <version
     or Unverified>`.
   - `## What it does` — 2–4 sentences, user outcome.
   - `## Requirements` — only if non-obvious, else delete.
   - `## Enable` — numbered steps: install handle, settings toggle,
     env var, queue worker.
   - `## Configure` — table of key / env var / default / effect, then
     a `env` and/or `php` sample showing the real names.
   - `## Use` — the CP path, console command, or Twig/event call the
     user actually touches, with one fenced example and its expected
     output.
   - `## Verify` — what to look at to confirm success (Langfuse trace
     fields, log line, CP screen).
   - `## Troubleshoot` — symptom → cause → fix table, reproduced
     issues only.
   - `## Related` — links only.

3. Update `README.md`:
   - Add one bullet under `## Features`: `- [<feature>]({{PATH}}) —
     <one line>`.
   - Do not paste the feature content into `README.md`.
   - If `composer.json` / `src/Plugin.php` exist and the README still
     lacks `## Requirements`, `## Installation`, or `## Configuration`,
     add them in the convention's order, sourced from the code — never
     invented.
   - Keep the root README ≤ 120 lines.
4. Self-check:
   - [ ] No `{{`, `TODO`, `TBD` left.
   - [ ] Every option/env var/command/CP path traced to code.
   - [ ] Every code fence has a language tag.
   - [ ] ≤ 90 lines; short sentences; no "simply"/"just"/"easy".
   - [ ] Nothing documents *why* the feature is built that way.
5. Run `make check` and fix anything it reports.

## Reply format

Report, in this order: the file written, the README bullet added, the
`make check` result, and a `Unverified` list (or "none"). Nothing else.
