SHELL := /bin/sh
.DEFAULT_GOAL := help

FEATURE ?=
DATE := $(shell date +%Y-%m-%d)

slug = $(shell printf '%s' '$(1)' | tr '[:upper:]' '[:lower:]' | sed -e 's/[^a-z0-9]\{1,\}/-/g' -e 's/^-//' -e 's/-$$//')
SLUG := $(call slug,$(FEATURE))

LAST := $(shell ls docs/features/*.md 2>/dev/null | sed -n 's|.*/\([0-9][0-9]\)-.*|\1|p' | sort -n | tail -1)
NEXT := $(shell echo '$(LAST)' | awk '{ n = $$0 } END { if (n == "") print "01"; else printf "%02d", n + 1 }')

.PHONY: help agents context docs features feature check docs-check qa fix test

help:
	@printf '%s\n' \
	  'craft-langfuse-integration' \
	  '' \
	  '  make context                    print shared context (product, rules, ADR list)' \
	  '  make agents                     print AGENTS.md (the constraints)' \
	  '  make docs                       list all docs' \
	  '  make features                   list shipped feature docs' \
	  '  make feature FEATURE="<name>"   render the feature-README prompt for <name>' \
	  '  make check                      the gate: docs contract + composer check' \
	  '  make docs-check                 docs contract only (no mago/PHPUnit)' \
	  '  make qa                         composer check: lint, format, analyze, rules, tests' \
	  '  make fix                        auto-fix lint + format, then re-run make check' \
	  '  make test                       PHPUnit only' \
	  ''

agents:
	@cat AGENTS.md

context:
	@for f in docs/product/*.md docs/rules/*.md; do \
	  printf '\n===== %s =====\n' "$$f"; \
	  cat "$$f"; \
	done
	@printf '\n===== ADRs -- read the ones that touch your change =====\n'
	@ls docs/decision/*.md 2>/dev/null || printf '  (none)\n'

docs:
	@find docs -name '*.md' | sort

features:
	@ls docs/features/*.md 2>/dev/null || printf 'none yet -- run: make feature FEATURE="<name>"\n'

feature:
	@if [ -z '$(FEATURE)' ]; then \
	  printf 'Usage: make feature FEATURE="queued flush"\n' >&2; \
	  exit 1; \
	fi
	@mkdir -p docs/features
	@printf 'Feature:     %s\nOutput file: docs/features/%s-%s.md\n\n' '$(FEATURE)' '$(NEXT)' '$(SLUG)'
	@bash bin/render-feature-prompt.sh '$(FEATURE)' '$(NEXT)' '$(SLUG)' '$(DATE)' 'docs/features/$(NEXT)-$(SLUG).md'

check: docs-check qa

docs-check:
	@bash bin/check-docs.sh

qa:
	@if [ ! -f vendor/bin/mago ]; then \
	  printf 'vendor/ is missing -- run: composer install\n' >&2; \
	  exit 1; \
	fi
	@composer check

fix:
	@if [ ! -f vendor/bin/mago ]; then \
	  printf 'vendor/ is missing -- run: composer install\n' >&2; \
	  exit 1; \
	fi
	@composer lint:fix
	@composer format

test:
	@if [ ! -f vendor/bin/phpunit ]; then \
	  printf 'vendor/ is missing -- run: composer install\n' >&2; \
	  exit 1; \
	fi
	@composer test
