<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations;

/**
 * Fixture: EXPECTED violation of `craft/no-facade-log` through the root
 * `Log` alias (no import statement involved).
 */
final class UsesAliasedFacadeLog
{
    public function record(): void
    {
        Log::error('trace dropped');
    }
}
