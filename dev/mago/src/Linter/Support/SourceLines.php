<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Support;

use Mago\Sdk\Span;

use function max;
use function min;
use function strrpos;
use function strlen;
use function substr;
use function substr_count;
use function trim;

/**
 * Maps byte offsets in a source file to 1-based line numbers.
 */
final readonly class SourceLines
{
    public function __construct(
        private string $contents
    ) {
    }

    public function lineAt(int $offset): int
    {
        $offset = max(0, min($offset, strlen($this->contents)));

        if ($offset === 0) {
            return 1;
        }

        return substr_count(haystack: $this->contents, needle: "\n", offset: 0, length: $offset) + 1;
    }

    public function startLine(Span $span): int
    {
        return $this->lineAt($span->start);
    }

    /**
     * The line the span *ends* on, so a span that stops right after a newline
     * does not count the following line.
     */
    public function endLine(Span $span): int
    {
        return $this->lineAt($span->end > $span->start ? $span->end - 1 : $span->start);
    }

    public function singleLine(Span $span): bool
    {
        return $this->startLine($span) === $this->endLine($span);
    }

    /**
     * The text between the previous newline and the offset.
     */
    public function linePrefix(int $offset): string
    {
        $prefix = substr($this->contents, offset: 0, length: max(0, min($offset, strlen($this->contents))));
        $newline = strrpos(haystack: $prefix, needle: "\n");

        return $newline === false ? $prefix : substr($prefix, $newline + 1);
    }

    /**
     * Whether something other than whitespace precedes the offset on its line.
     */
    public function hasCodeBefore(int $offset): bool
    {
        return trim($this->linePrefix($offset)) !== '';
    }
}
