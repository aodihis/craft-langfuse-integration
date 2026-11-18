<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago\Linter\Support;

use function explode;
use function str_starts_with;
use function substr;
use function trim;

/**
 * Classifies comment text: prose lines versus tool tags.
 */
final readonly class CommentText
{
    private function __construct()
    {
    }

    /**
     * A docblock that opens with an `@tag` line, e.g. `@var` or `@return array{…}`.
     */
    public static function isTagOnly(string $text): bool
    {
        if (!str_starts_with($text, '/**')) {
            return false;
        }

        return str_starts_with(self::firstBodyLine($text), '@');
    }

    /**
     * Number of lines that carry human prose; a tag block counts as zero.
     */
    public static function proseLineCount(string $text): int
    {
        if (self::isTagOnly($text)) {
            return 0;
        }

        $count = 0;

        foreach (self::bodyLines($text) as $line) {
            if ($line === '' || str_starts_with($line, '@')) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    private static function firstBodyLine(string $text): string
    {
        foreach (self::bodyLines($text) as $line) {
            if ($line !== '') {
                return $line;
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private static function bodyLines(string $text): array
    {
        $body = match (true) {
            str_starts_with($text, '/*') => substr($text, offset: 2, length: -2),
            str_starts_with($text, '//') => substr($text, offset: 2),
            str_starts_with($text, '#') => substr($text, offset: 1),
            default => $text
        };

        $lines = [];
        foreach (explode("\n", $body) as $line) {
            $lines[] = trim(string: $line, characters: " \t\r\n*");
        }

        return $lines;
    }
}
