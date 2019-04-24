<?php

namespace Akeneo\Tool\Component\FileStorage;

final class Path
{
    /** @var array */
    private $directories;

    public function __construct(string ...$directories)
    {
        $this->directories = $directories;
    }

    public function __toString(): string
    {
        return array_reduce($this->directories, function ($path, $directory) {
            $directory = basename($directory);

            if ('' === $path) {
                return $directory;
            }

            return $path.DIRECTORY_SEPARATOR.$directory;
        }, '');
    }
}
