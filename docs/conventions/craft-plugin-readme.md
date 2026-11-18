# Convention — Craft plugin README

**Type:** Convention (working doc)
**Status:** Accepted
**Date:** 2026-11-16
**Applies to:** root `README.md` and every `docs/features/NN-slug.md`

Craft plugin READMEs are read by a developer who is already in the CP,
already knows Composer, and wants to know four things in this order:
**does it run on my stack → how do I install it → what do I configure →
how do I use this specific feature**. Everything else is filler.

This file is the checked-in reference. `make check` enforces the headings
listed in §3; it cannot enforce tone — that is on you.

---

## 1. Section order (root README)

| # | Section | Content | Rules |
|---|---|---|---|
| 1 | Title | Plugin name as in the Plugin Store | No emoji, no badge wall. One line of positioning under it. |
| 2 | Requirements | Craft version, PHP range, extensions, services | Exact constraints (`^6.0`, `^8.4`), never "Craft 6+". |
| 3 | Installation | `composer require`, then install | Show both paths: CP (Settings → Plugins) and console (`php craft plugin/install <handle>`). |
| 4 | Configuration | `.env` vars, config file, settings screen | Table: name, type, default, required. Real defaults only. |
| 5 | Features | One bullet per feature, link to `docs/features/NN-slug.md` | One line each, no marketing. |
| 6 | Troubleshooting / FAQ | Symptom → cause → fix | Only symptoms you have actually reproduced. |
| 7 | Support | Issue tracker URL | |
| 8 | License | SPDX id (`MIT`, `proprietary`) | |

Omit a section rather than shipping an empty one. Never invent a
"Roadmap" section.

## 2. Style rules

- **Commands are copy-pasteable**: fenced `bash`/`shell`, no `$` prompt
  prefix, no `<placeholder>` inside a runnable line unless it is
  obviously a value.
- **Code fences always get a language**: `php`, `bash`, `twig`,
  `yaml`, `env`, `text`.
- **Env vars** are shown as a `env` block with the exact names used in
  code, then a table of meanings. Never prose-only.
- **Settings** name the CP path verbatim (`Settings → Plugins →
  Langfuse`) and the settings keys as they appear in `Settings::$defineRules()`.
- **Console commands** use a table: command, purpose, options.
- **No screenshots of text.** Paste the text.
- **Short sentences. Second person. Imperative** for steps.
- **Length budget:** root README ≤ 120 lines; a feature doc 40–90
  lines. If it does not fit, the feature is doing too much.
- **Tense:** describe what the current released version does. No
  "will", no "planned", no "coming soon".
- **Craft handles:** use the plugin handle (`langfuse`) everywhere it is
  user-visible — `php craft langfuse/...`, `LANGFUSE_*`, `craft.langfuse`.

## 3. Feature doc structure (required)

`docs/features/NN-slug.md` — one file per shipped feature. Headings in
this exact order; `make check` verifies the bolded ones.

Header block, then these headings in this order:

1. `# NN — <Feature name>` then `**Type:** Feature usage`,
   `**Status:** Shipped`, `**Date:** YYYY-MM-DD`, `**Since:** x.y.z`
   (same header style as `docs/product/` and `docs/decision/`).
2. **`## What it does`** — what the user gets, 2–4 sentences. No
   architecture, no rationale.
3. `## Requirements` — Craft/PHP constraints or features this depends
   on. Delete the section if there are none.
4. **`## Enable`** — exact steps: install handle, setting toggle, env
   var, queue worker.
5. **`## Configure`** — table of options (key, env var, default,
   effect) plus a `env` code block and/or config file sample.
6. **`## Use`** — the actual thing the user does: CP path, console
   command, Twig call, or event. At least one fenced example with
   expected output.
7. **`## Verify`** — how the user confirms it works: what to look at
   (Langfuse trace, log line, CP screen) and what success looks like.
8. `## Troubleshoot` — symptom → cause → fix table. Reproduced issues
   only.
9. `## Related` — links to the product doc / ADR / rules that own the
   decisions. Links only, do not restate them.

`make check` requires all headings except `## Requirements` and
`## Related`.

## 4. Naming

- Files: `NN-slug.md`, zero-padded, sequential, lowercase,
  hyphen-separated (`03-queued-flush.md`). Numbers are never reused.
- Headings: sentence case, no numbering inside a doc.
- Section names are fixed by §3 — do not rename them per feature, or
  `make check` and grep-based tooling break.

## 5. Anti-patterns (reject the draft if any appear)

- A feature doc that explains *why* a decision was made instead of *how
  to use* it → move to `docs/decision/`.
- Options, env vars, or CP screens that do not exist in the code.
- "Simply", "just", "easy" — the reader decides that.
- Copy-pasted install section in every feature doc → link to the root
  README instead.
- Placeholder text (`{{ }}`, `TODO`, `TBD`, `lorem`) left behind.
- Screenshots, animated GIFs, badge walls.
- Changelog entries inside a feature doc ("new in 2.1").
