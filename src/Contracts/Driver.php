<?php

namespace Orbit\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface Driver
{
    public function shouldRestoreCache($directory);

    public function save(Model $model, $directory);

    public function delete(Model $model, $directory);

    public function all($directory);
}
