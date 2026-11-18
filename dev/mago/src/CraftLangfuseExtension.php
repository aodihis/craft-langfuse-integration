<?php

declare(strict_types=1);

namespace aodihis\craftlangfuse\Dev\Mago;

use aodihis\craftlangfuse\Dev\Mago\Linter\Rules\CommentPlacementRule;
use aodihis\craftlangfuse\Dev\Mago\Linter\Rules\ForbiddenImportsRule;
use aodihis\craftlangfuse\Dev\Mago\Linter\Rules\NoFacadeLogRule;
use aodihis\craftlangfuse\Dev\Mago\Linter\Rules\NoSingleLineControlStructureRule;
use aodihis\craftlangfuse\Dev\Mago\Linter\Rules\ReadonlyValueObjectsRule;
use Mago\Sdk\Extension;

/**
 * Mago extension bundling this plugin's custom linter rules.
 * @internal
 */
final class CraftLangfuseExtension
{
    private function __construct()
    {
    }

    public static function create(): Extension
    {
        return new Extension(
            identifier: 'craft/langfuse-rules',
            name: 'Craft Langfuse integration project rules',
            version: '0.1.0',
            linterRules: [
                new CommentPlacementRule(),
                new ForbiddenImportsRule(),
                new NoFacadeLogRule(),
                new NoSingleLineControlStructureRule(),
                new ReadonlyValueObjectsRule()
            ]
        );
    }
}
