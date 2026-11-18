<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Clean;

/**
 * Fixture: EXPECTED to be clean — a tag-only docblock may sit anywhere, and its
 * continuation lines are part of the tag, not prose.
 */
final class TagOnlyDocblocks
{
    /**
     * @return array{
     *     counters: array<string, int>,
     *     total: int
     * }
     */
    public function summary(): array
    {
        /**
         * @var array{
         *     counters: array<string, int>,
         *     total: int
         * }
         */
        $summary = ['counters' => [], 'total' => 0];

        return $summary;
    }
}
