<?php

namespace Orbit\Drivers;

use Illuminate\Database\Eloquent\Model;
use SplFileInfo;

class Json extends FileDriver
{
    protected function dumpContent(Model $model)
    {
        $data = array_filter($this->getModelAttributes($model), function ($value) {
            return $value !== null;
        });

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function parseContent(SplFileInfo $file)
    {
        $contents = file_get_contents($file->getPathname());

        if (! $contents) {
            return [];
        }

        return json_decode($contents, true);
    }

    protected function extension()
    {
        return 'json';
    }
}
