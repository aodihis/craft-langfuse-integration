<?php

declare(strict_types=1);

use aodihis\craftlangfuse\Dev\Mago\CraftLangfuseExtension;
use Mago\Sdk\Worker;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Worker(
    CraftLangfuseExtension::create(),
))->run();
