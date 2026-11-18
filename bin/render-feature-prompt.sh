#!/usr/bin/env bash
# Renders docs/prompts/generate-feature-readme.md for one feature.
# Usage: render-feature-prompt.sh <feature> <NN> <slug> <date> <path>
# Called by `make feature FEATURE="..."`.
set -euo pipefail

cd "$(dirname "$0")/.." || exit 1

if [ "$#" -ne 5 ]; then
  printf 'usage: %s <feature> <NN> <slug> <date> <path>\n' "$0" >&2
  exit 2
fi

feature=$1 nn=$2 slug=$3 date=$4 path=$5

# Escape the characters sed treats specially in a replacement.
esc() { printf '%s' "$1" | sed -e 's/[\\&|]/\\&/g'; }

sed \
  -e "s|{{FEATURE}}|$(esc "$feature")|g" \
  -e "s|{{NN}}|$(esc "$nn")|g" \
  -e "s|{{SLUG}}|$(esc "$slug")|g" \
  -e "s|{{DATE}}|$(esc "$date")|g" \
  -e "s|{{PATH}}|$(esc "$path")|g" \
  docs/prompts/generate-feature-readme.md
