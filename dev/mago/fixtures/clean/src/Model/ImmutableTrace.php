<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Clean\Model;

use DateTimeImmutable;

/**
 * Fixture: EXPECTED to be clean — a readonly value object under src/Model/.
 */
final readonly class ImmutableTrace
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public string $id,
        public string $name,
        public DateTimeImmutable $startedAt,
        public array $tags = [],
    ) {
    }
}
