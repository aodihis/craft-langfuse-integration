<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Clean;

use Psr\Log\LoggerInterface;

/**
 * Fixture: EXPECTED to be clean — the injected `Psr\Log\LoggerInterface` is
 * the sanctioned way to log (as opposed to the `Log` facade).
 */
final readonly class InjectedLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function record(string $message): void
    {
        $this->logger->info($message, ['source' => 'craft-langfuse-integration']);
    }
}
