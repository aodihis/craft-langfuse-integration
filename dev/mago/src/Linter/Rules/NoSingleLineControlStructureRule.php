<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Rules;

use aodihis\craftlangfuse\Dev\Mago\Linter\Support\SourceLines;
use Mago\Sdk\Linter\LintContext;
use Mago\Sdk\Linter\Rule;
use Mago\Sdk\Linter\RuleDefinition;
use Mago\Sdk\Reporting\Issue;
use Mago\Sdk\Reporting\Level;
use Mago\Sdk\Syntax\NodeKind;

/**
 * Rejects control structures written on a single line, e.g.
 * `if ($ready) { return; }` or `foreach ($rows as $row) { $sum += $row; }`.
 * The condition, each brace, and the body get their own lines (PSR-12).
 * @internal
 */
final class NoSingleLineControlStructureRule implements Rule
{
    private const array TARGETS = [
        NodeKind::If,
        NodeKind::IfStatementBodyElseIfClause,
        NodeKind::IfStatementBodyElseClause,
        NodeKind::For,
        NodeKind::Foreach,
        NodeKind::While,
        NodeKind::DoWhile
    ];

    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            code: 'craft/no-single-line-control-structure',
            name: 'No single-line control structures',
            description: 'Forbids writing an if/else, loop header and its body on one line.',
            defaultLevel: Level::Error,
            defaultEnabled: true,
            targets: self::TARGETS
        );
    }

    public function lint(LintContext $context): void
    {
        $lines = new SourceLines($context->file->contents);
        if (!$lines->singleLine($context->node->span)) {
            return;
        }

        $context->report(Issue::new(
            'Do not write a control structure and its body on a single line.',
            $context->node->span,
            annotationMessage: 'Single-line control structure'
        )->withHelp(
            'Put the condition, the opening brace, the body and the closing brace on their own lines '
            . '(PSR-12) so diffs and breakpoints stay readable. See docs/rules/toolchain.md.'
        ));
    }
}
