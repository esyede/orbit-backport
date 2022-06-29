<?php

namespace Orbit\Models;

use Illuminate\Database\Eloquent\Model;

final class OrbitMeta extends Model
{
    protected $table = '_orbit_meta';
    protected $connection = 'orbit_meta';
    protected $guarded = [];

    public $timestamps = false;

    public function orbital()
    {
        $class = $this->orbital_type;
        return $class::find($this->orbital_key);
    }

    public static function forOrbital(Model $model)
    {
        return static::query()
            ->where('orbital_type', get_class($model))
            ->where('orbital_key', $model->getKey())
            ->first();
    }
}
