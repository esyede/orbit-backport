<?php

namespace Orbit\Actions;

use Orbit\Facades\Orbit;

class ClearCache
{
    public function __invoke(): void
    {
        $path = Orbit::getDatabasePath();

        if (! is_file($path)) {
            return;
        }

        unlink($path);
    }
}
