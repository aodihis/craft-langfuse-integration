<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations;

/**
 * Fixture: EXPECTED violations of `craft/comment-placement`.
 */
final class NoisyComments
{
    // fixed the return value here
    private const int LIMIT = 5;

    public function run(int $value): int
    {
        // limit it
        $value = min($value, self::LIMIT); // never above the limit

        if ($value > 0) {
            return $value;
        }

        return 0;
    }
}
