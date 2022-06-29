<?php

namespace Orbit;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Support
{
    public static function buildPathForPattern($pattern, Model $model)
    {
        $parts = explode('/', $pattern);
        $path = '';

        foreach ($parts as $i => $part) {
            if ($i !== 0) {
                $path .= '/';
            }

            if (Str::startsWith($part, '{') && Str::endsWith($part, '}')) {
                $binding = explode(':', trim($part, '{}'));
                list($property, $args) = (count($binding) > 1)
                    ? [$binding[0], explode(',', $binding[1])]
                    : [$binding[0], []];

                $value = $model->{$property};

                if ($value instanceof Carbon && isset($args[0])) {
                    $path .= $value->format($args[0]);
                } else {
                    $path .= (string) $value;
                }
            } else {
                $path .= $part;
            }
        }

        return $path;
    }
}
