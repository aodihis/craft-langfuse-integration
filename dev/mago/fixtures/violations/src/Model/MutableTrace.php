<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Violations\Model;

/**
 * Fixture: EXPECTED violation of `craft/readonly-value-objects`
 * (a model class declared without `readonly`).
 */
final class MutableTrace
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
