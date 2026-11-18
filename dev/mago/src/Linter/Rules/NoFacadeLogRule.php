<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Rules;

use Mago\Sdk\Exception\InvalidArgumentException;
use Mago\Sdk\Linter\LintContext;
use Mago\Sdk\Linter\Rule;
use Mago\Sdk\Linter\RuleDefinition;
use Mago\Sdk\Reporting\Issue;
use Mago\Sdk\Reporting\Level;
use Mago\Sdk\Syntax\NodeKind;
use Mago\Sdk\Syntax\ResolvedName;

use function ltrim;
use function str_ends_with;
use function strtolower;

/**
 * Bans references to the `Log` facade — import, call site, and FQN usage.
 * The root `Log` alias resolves to `<namespace>\Log` outside a Laravel
 * bootstrap, so the written text of the resolved span is matched instead.
 * See docs/rules/toolchain.md.
 * @internal
 */
final class NoFacadeLogRule implements Rule
{
    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            code: 'craft/no-facade-log',
            name: 'No Log facade',
            description: 'Forbids imports and calls of the `Log` facade; the library must use an injected Psr\\Log\\LoggerInterface.',
            defaultLevel: Level::Error,
            defaultEnabled: true,
            targets: [NodeKind::Program]
        );
    }

    public function lint(LintContext $context): void
    {
        foreach ($context->file->getResolvedNames() as $resolved) {
            if (!$this->isLogFacadeReference($context, $resolved)) {
                continue;
            }

            $context->report(Issue::new(
                "Use an injected `Psr\\Log\\LoggerInterface` instead of the `{$resolved->name}` facade.",
                $resolved->span,
                annotationMessage: 'Log facade reference'
            )->withHelp(
                'The library must not use the `Log` facade at package level. Inject a '
                . '`Psr\\Log\\LoggerInterface` and log through it. See docs/rules/toolchain.md.'
            ));
        }
    }

    private function isLogFacadeReference(LintContext $context, ResolvedName $resolved): bool
    {
        try {
            $written = strtolower(ltrim(string: $context->file->getText($resolved->span), characters: '\\'));
        } catch (InvalidArgumentException) {
            return false;
        }

        return $written === 'log' || str_ends_with($written, 'facades\\log');
    }
}
