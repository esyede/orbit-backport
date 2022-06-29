<?php

namespace Orbit\Drivers;

use FilesystemIterator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Orbit\Contracts\Driver as DriverContract;
use Orbit\Facades\Orbit;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

abstract class FileDriver implements DriverContract
{
    public function shouldRestoreCache($directory)
    {
        $highest = 0;

        foreach (new FilesystemIterator($directory) as $file) {
            if ($file->getMTime() > $highest) {
                $highest = $file->getMTime();
            }
        }

        return $highest > filemtime(Orbit::getDatabasePath());
    }

    public function save(Model $model, $directory)
    {
        if ($model->wasChanged($model->getKeyName())) {
            unlink($this->filepath($directory, $model->getOriginal($model->getKeyName())));
        }

        $path = $this->filepath($directory, $model->getKey());

        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        file_put_contents($path, $this->dumpContent($model));

        return true;
    }

    public function delete(Model $model, $directory)
    {
        unlink($this->filepath($directory, $model->getKey()));

        return true;
    }

    public function all($directory)
    {
        $collection = Collection::make();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $files = $this->all($file->getRealPath());

                $collection->merge($files);

                continue;
            }

            if ($file->getExtension() !== $this->extension()) {
                continue;
            }

            $collection->push(array_merge($this->parseContent($file), [
                'file_path_read_from' => $file->getRealPath(),
            ]));
        }

        return $collection;
    }

    public function filepath($directory, $key)
    {
        return $directory . DIRECTORY_SEPARATOR . $key . '.' . $this->extension();
    }

    protected function getModelAttributes(Model $model)
    {
        return collect($model->getAttributes())
            ->map(function ($unused, $key) {
                return $model->{$key};
            })
            ->toArray();
    }

    abstract protected function extension();

    abstract protected function dumpContent(Model $model);

    abstract protected function parseContent(SplFileInfo $file);
}
