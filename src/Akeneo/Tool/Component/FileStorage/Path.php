<?php

namespace Akeneo\Tool\Component\FileStorage;

final class Path
{
    /** @var array */
    private $directories;

    /**  @var string */
    private $storage;

    public function __construct(string $storage, string ...$directories)
    {
        $this->directories = $directories;
        $this->storage = $storage;
    }

    public static function withoutStorage(string ...$directories): self
    {
        return new self('', ...$directories);
    }

    public function __toString(): string
    {
        $path = array_reduce($this->directories, function ($path, $directory) {
            $directory = trim($directory, "/");

            if ('' === $path) {
                return $directory;
            }

            return $path.DIRECTORY_SEPARATOR.$directory;
        }, '');

        if ('' !== $this->storage) {
            return $this->storage.'://'.$path;
        }

        return $path;
    }
}
