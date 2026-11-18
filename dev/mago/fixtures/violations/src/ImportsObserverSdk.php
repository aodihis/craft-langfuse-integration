<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations;

use LangSmith\Client;

/**
 * Fixture: EXPECTED violations of `craft/forbidden-imports`
 * (the `use` statement and the resolved type hint).
 */
final class ImportsObserverSdk
{
    public function export(Client $client): void
    {
        $client->trace();
    }
}
