<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Support;

use Mago\Sdk\Span;
use Mago\Sdk\Syntax\NodeKind;
use Mago\Sdk\Syntax\SourceFile;
use Mago\Sdk\Syntax\Trivia;

use function trim;

/**
 * Splits a file's comments into blocks of consecutive comment lines.
 */
final readonly class CommentBlocks
{
    private function __construct()
    {
    }

    /**
     * @return list<CommentBlock>
     */
    public static function fromFile(SourceFile $file): array
    {
        $lines = new SourceLines($file->contents);
        $blocks = [];
        $current = [];
        $previous = null;

        foreach ($file->getTrivia() as $comment) {
            if ($previous instanceof Trivia && $current !== []) {
                $between = $file->getText(new Span($previous->span->end, $comment->span->start));
                $nextLine = $lines->startLine($comment->span);
                $isSeparated = trim($between) !== '' || $nextLine > ( $lines->endLine($previous->span) + 1 );

                if ($isSeparated) {
                    $blocks[] = new CommentBlock($file, $current);
                    $current = [];
                }
            }

            $current[] = $comment;
            $previous = $comment;
        }

        if ($current !== []) {
            $blocks[] = new CommentBlock($file, $current);
        }

        return $blocks;
    }

    /**
     * @param list<NodeKind> $kinds
     *
     * @return array<int, true> first line of every declaration of those kinds
     */
    public static function declarationLines(SourceFile $file, array $kinds): array
    {
        $lines = new SourceLines($file->contents);
        $declared = [];

        foreach ($kinds as $kind) {
            foreach ($file->getNodes($kind) as $node) {
                $declared[$lines->startLine($node->span)] = true;
            }
        }

        return $declared;
    }
}
