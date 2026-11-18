<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Fixtures\Clean;

/**
 * Fixture: EXPECTED to be clean — one short docblock above a class, one above
 * a method, and a tag-only docblock inside the body.
 */
final readonly class DocumentedFunction
{
    public function run(int $value): int
    {
        /** @var array<int, int> $steps */
        $steps = [];

        if ($value > 10) {
            $value = 10;
        }

        foreach ($steps as $step) {
            $value += $step;
        }

        return $value;
    }
}
