<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Rules;

use Mago\Sdk\Linter\LintContext;
use Mago\Sdk\Linter\Rule;
use Mago\Sdk\Linter\RuleDefinition;
use Mago\Sdk\Reporting\Issue;
use Mago\Sdk\Reporting\Level;
use Mago\Sdk\Syntax\NodeKind;
use Mago\Sdk\Syntax\ResolvedName;

use function str_starts_with;
use function strtolower;

/**
 * Bans agent/provider events, the observer library's internals, other
 * exporter SDKs, and HTTP clients: only the normalized record crosses into
 * this adapter. Every resolved name is checked, so imports, type hints, catch
 * clauses and attribute classes are all caught. See docs/rules/architecture.md.
 * @internal
 */
final class ForbiddenImportsRule implements Rule
{
    private const array FORBIDDEN_PREFIXES = [
        'CraftAiObserver\\Tree\\',
        'CraftAiObserver\\Dev\\',
        'Laravel\\Ai\\',
        'LangSmith\\',
        'OpenTelemetry\\',
        'Psr\\Http\\',
        'GuzzleHttp\\',
        'Symfony\\Component\\HttpClient\\'
    ];

    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            code: 'craft/forbidden-imports',
            name: 'Forbidden observer / provider imports',
            description: 'Disallows agent events, observer-library internals, other exporter SDKs, '
            . 'and HTTP clients inside the Langfuse adapter.',
            defaultLevel: Level::Error,
            defaultEnabled: true,
            targets: [NodeKind::Program]
        );
    }

    public function lint(LintContext $context): void
    {
        foreach ($context->file->getResolvedNames() as $resolved) {
            $prefix = $this->forbiddenPrefixOf($resolved);
            if ($prefix === null) {
                continue;
            }

            $context->report(Issue::new(
                "Reference to `{$resolved->name}` violates the architecture rules: "
                . 'this plugin is a Langfuse adapter, not a listener, mapper or HTTP layer.',
                $resolved->span,
                "Forbidden namespace `{$resolved->name}`"
            )->withHelp(
                'craft-ai-observer owns listening, normalization and the span tree; this plugin maps '
                . '`NormalizedRecord` to Langfuse and flushes. Other exporters get their own plugin, '
                . 'and the Langfuse client is the only transport. See docs/rules/architecture.md.'
            ));
        }
    }

    private function forbiddenPrefixOf(ResolvedName $resolved): ?string
    {
        $lower = strtolower($resolved->name);
        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($lower, strtolower($prefix))) {
                return $prefix;
            }
        }

        return null;
    }
}
