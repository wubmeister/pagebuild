<?php

namespace CoreKit;

class Resolver
{
    protected $paths = [];

    public function addPath($path)
    {
        $this->$paths[] = rtrim($path, '/') . '/';
    }

    public function resolve($file)
    {
        $file = ltrim($file, '/');
        foreach ($this->paths as $path) {
            if (file_exists($path.$file)) {
                return $path.$file;
            }
        }

        return null;
    }
}
