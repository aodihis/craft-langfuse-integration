<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations;

/**
 * Fixture: EXPECTED violation of `no-nested-ternary`.
 */
final class TernarySoup
{
    public function weigh(int $weight): string
    {
        return $weight > 10 ? ($weight > 20 ? 'heavy' : 'medium') : 'light';
    }
}
