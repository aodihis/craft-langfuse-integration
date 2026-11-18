#!/usr/bin/env bash
# Validates the docs contract described in AGENTS.md and
# docs/conventions/craft-plugin-readme.md. Run via `make check`.
set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

fail=0
ok()  { printf '  ok    %s\n' "$1"; }
bad() { printf '  FAIL  %s\n' "$1"; fail=1; }

need_file() {
  if [ -f "$1" ]; then ok "$1"; else bad "missing $1"; fi
}

need_grep() { # file, pattern, label
  if grep -qF -- "$2" "$1" 2>/dev/null; then ok "$1 :: $3"; else bad "$1 :: $3"; fi
}

printf '\n-- required files\n'
need_file AGENTS.md
need_file README.md
need_file Makefile
need_file docs/conventions/craft-plugin-readme.md
need_file docs/prompts/generate-feature-readme.md
need_file docs/rules/architecture.md
need_file docs/rules/backend.md
need_file docs/rules/logic.md
need_file docs/rules/toolchain.md
need_file mago.toml
need_file .mago/craft-worker.php
need_file dev/mago/verify.php
need_file composer.json

printf '\n-- AGENTS.md keeps the promised sections\n'
need_grep AGENTS.md '## 1. Hard constraints' 'hard constraints'
need_grep AGENTS.md '## 2. How to work with the docs' 'docs workflow'
need_grep AGENTS.md '## 3. Definition of done' 'definition of done'
need_grep AGENTS.md '## 4. Commands' 'commands'

printf '\n-- naming patterns\n'
shopt -s nullglob
adrs=(docs/decision/*.md)
for f in "${adrs[@]}"; do
  base="$(basename "$f")"
  case "$base" in
    [0-9][0-9][0-9][0-9]-*.md) case "$base" in *[A-Z]*|*' '*) bad "ADR name must be lowercase-hyphen: $base" ;; *) ok "ADR $base" ;; esac ;;
    *) bad "ADR name must be NNNN-slug.md: $base" ;;
  esac
done

products=(docs/product/*.md)
for f in "${products[@]}"; do
  base="$(basename "$f")"
  case "$base" in
    [0-9][0-9]-*.md) ok "product doc $base" ;;
    *) bad "product doc must be NN-slug.md: $base" ;;
  esac
done

printf '\n-- the gate is wired\n'
need_grep composer.json 'verify-rules' 'composer verify-rules script'
need_grep composer.json '"check"' 'composer check script'
need_grep mago.toml '.mago/craft-worker.php' 'mago extension host'

printf '\n-- every custom mago rule is documented\n'
for f in dev/mago/src/Linter/Rules/*.php; do
  code=$(sed -n "s/.*code: '\(craft\/[a-z-]*\)'.*/\1/p" "$f")
  if [ -z "$code" ]; then
    bad "$f declares no craft/* rule code"
    continue
  fi

  if grep -qF -- "$code" docs/rules/toolchain.md; then
    ok "documented $code"
  else
    bad "$code is missing from docs/rules/toolchain.md"
  fi
done

printf '\n-- feature docs\n'
features=(docs/features/*.md)
if [ ${#features[@]} -eq 0 ]; then
  ok 'no feature docs yet (allowed)'
fi

required_sections=(
  '## What it does'
  '## Enable'
  '## Configure'
  '## Use'
  '## Verify'
  '## Troubleshoot'
)

for f in "${features[@]}"; do
  base="$(basename "$f")"
  case "$base" in
    [0-9][0-9]-*.md) ok "$f name" ;;
    *) bad "$f must be NN-slug.md" ;;
  esac

  for s in 'Feature usage' 'Status:' 'Date:'; do
    need_grep "$f" "$s" "header has $s"
  done

  for s in "${required_sections[@]}"; do
    if grep -qxF -- "$s" "$f"; then ok "$f :: $s"; else bad "$f :: missing heading $s"; fi
  done

  if grep -nE '\{\{|TODO|TBD' "$f" >/dev/null 2>&1; then
    bad "$f :: placeholders/TODOs left"
  else
    ok "$f :: no placeholders"
  fi

  if grep -qF -- "$f" README.md 2>/dev/null; then
    ok "$f :: linked from README.md"
  else
    bad "$f :: not linked from README.md"
  fi
done

printf '\n-- README budget\n'
lines=$(wc -l < README.md)
if [ "$lines" -le 120 ]; then ok "README.md is $lines lines (<=120)"; else bad "README.md is $lines lines (budget 120)"; fi

printf '\n'
if [ "$fail" -ne 0 ]; then
  printf 'make check: FAILED\n\n'
  exit 1
fi
printf 'make check: passed\n\n'
