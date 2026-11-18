<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Rules;

use aodihis\craftlangfuse\Dev\Mago\Linter\Support\CommentBlock;
use aodihis\craftlangfuse\Dev\Mago\Linter\Support\CommentBlocks;
use Mago\Sdk\Linter\LintContext;
use Mago\Sdk\Linter\Rule;
use Mago\Sdk\Linter\RuleDefinition;
use Mago\Sdk\Reporting\Issue;
use Mago\Sdk\Reporting\Level;
use Mago\Sdk\Syntax\NodeKind;

use function array_key_exists;
use function sprintf;

/**
 * Allows a short comment only above a function, method or class-like
 * declaration; tag-only docblocks (`@var`, …) are allowed anywhere, inline
 * notes never. See docs/rules/toolchain.md.
 * @internal
 */
final class CommentPlacementRule implements Rule
{
    private const int MAX_PROSE_LINES = 4;

    /**
     * @var non-empty-list<NodeKind>
     */
    private const array DOCUMENTABLE = [
        NodeKind::Function,
        NodeKind::Method,
        NodeKind::Class_,
        NodeKind::Interface,
        NodeKind::Trait,
        NodeKind::Enum
    ];

    public function getDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            code: 'craft/comment-placement',
            name: 'Comments only above declarations',
            description: 'Allows a short comment only directly above a function, method or class-like '
            . 'declaration; forbids inline comments and comments above code.',
            defaultLevel: Level::Error,
            defaultEnabled: true,
            targets: [NodeKind::Program]
        );
    }

    public function lint(LintContext $context): void
    {
        $file = $context->file;
        $declared = CommentBlocks::declarationLines($file, self::DOCUMENTABLE);

        foreach (CommentBlocks::fromFile($file) as $block) {
            if ($block->isMachineAnnotation()) {
                continue;
            }

            $problem = $this->problem($block, $declared);
            if ($problem === null) {
                continue;
            }

            $context->report(Issue::new(
                $problem,
                $block->last()->span,
                annotationMessage: 'Comment placement'
            )->withHelp(
                'Comments document an API: put a short block directly above the function, method or class '
                . 'it describes. Do not annotate individual lines or statements, and do not leave '
                . 'inline notes; use `@var`-style tag-only docblocks for tool annotations.'
            ));
        }
    }

    /**
     * @param array<int, true> $declared
     */
    private function problem(CommentBlock $block, array $declared): ?string
    {
        if ($block->isInline()) {
            return 'Inline comment: comments are not allowed next to code.';
        }

        if (!array_key_exists($block->endLine() + 1, $declared)) {
            return 'Comment is not directly above a function, method or class declaration.';
        }

        if ($block->proseLineCount() > self::MAX_PROSE_LINES) {
            return sprintf(
                'Comment has %d lines of prose: keep it to %d or move the detail into the docs.',
                $block->proseLineCount(),
                self::MAX_PROSE_LINES
            );
        }

        return null;
    }
}
