<?php

namespace Orbit\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Orbit\Concerns\Orbital;
use ReflectionClass;

class CacheCommand extends Command
{
    protected $name = 'orbit:cache';
    protected $descripition = 'Cache all Orbit models.';

    public function handle()
    {
        $models = $this->findOrbitModels();

        if ($models->isEmpty()) {
            $this->warn('Could not find any Orbit models.');
            return 0;
        }

        $models->each(function ($model) {
            return (new $model())->migrate();
        });

        $this->info('Cached the following Orbit models:');
        $this->newLine();
        $this->line($models->map(function ($model) {
            return '• <info>' . $model . '</info>';
        }));

        return 0;
    }

    protected function findOrbitModels(): Collection
    {
        $laravel = $this->getLaravel();

        return collect(File::allFiles($laravel->path()))
            ->map(function ($item) use ($laravel) {
                $path = $item->getRelativePathName();
                $class = sprintf(
                    '\%s%s',
                    $laravel->getNamespace().'\\',
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );

                return $class;
            })
            ->filter(function ($class) {
                if (! class_exists($class)) {
                    return false;
                }

                $reflection = new ReflectionClass($class);

                return $reflection->isSubclassOf(Model::class)
                    && ! $reflection->isAbstract()
                    && isset(class_uses_recursive($class)[Orbital::class]);
            });
    }
}
