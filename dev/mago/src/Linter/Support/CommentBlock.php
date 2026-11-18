<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Support;

use Mago\Sdk\Syntax\SourceFile;
use Mago\Sdk\Syntax\Trivia;

use function count;

/**
 * One comment block: consecutive comment lines treated as a unit.
 */
final readonly class CommentBlock
{
    private SourceLines $lines;

    /**
     * @param non-empty-list<Trivia> $comments
     */
    public function __construct(
        private SourceFile $file,
        private array $comments
    ) {
        $this->lines = new SourceLines($file->contents);
    }

    public function last(): Trivia
    {
        return $this->comments[count($this->comments) - 1];
    }

    public function startLine(): int
    {
        return $this->lines->startLine($this->comments[0]->span);
    }

    public function endLine(): int
    {
        return $this->lines->endLine($this->last()->span);
    }

    /**
     * Lines of prose in the block (delimiters, blank lines and `@tag` lines excluded).
     */
    public function proseLineCount(): int
    {
        $count = 0;
        foreach ($this->comments as $comment) {
            $count += CommentText::proseLineCount($this->file->getText($comment->span));
        }

        return $count;
    }

    /**
     * Whether code precedes the block on its first line.
     */
    public function isInline(): bool
    {
        return $this->lines->hasCodeBefore($this->comments[0]->span->start);
    }

    /**
     * Whether every comment in the block is a tag-only docblock.
     */
    public function isMachineAnnotation(): bool
    {
        foreach ($this->comments as $comment) {
            if (!CommentText::isTagOnly($this->file->getText($comment->span))) {
                return false;
            }
        }

        return true;
    }
}
