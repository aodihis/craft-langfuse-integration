<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations;

/**
 * Fixture: EXPECTED violations of `craft/no-single-line-control-structure`
 * and `block-statement`.
 */
final class OneLineBranches
{
    public function choose(int $value): int
    {
        if ($value > 0) { return 1; }

        foreach ([1, 2, 3] as $item) { $value += $item; }

        if ($value === 0) return 0;

        return $value;
    }
}
