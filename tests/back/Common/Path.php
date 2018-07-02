<?php

namespace Akeneo\Test\Common;

/**
 * Object that represent a directory path from the root directory of this application.
 *
 * For instance new (Path('foo', 'bar')); will return /application/root/directory/foo/bar
 * Beware, those Path may not exist yet on the filesystem. That's why realpath is not used here.
 */
final class Path
{
    /** @var array */
    private $directories;

    /**
     * @param string ...$directories
     */
    public function __construct(string ...$directories)
    {
        $this->directories = $directories;
    }

    /**
     * Return the relative path
     *
     * @return string
     */
    public function relativePath(): string
    {
        $path = implode(DIRECTORY_SEPARATOR, $this->directories);

        return $path;
    }

    /**
     * Return the absolute path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function absolutePath(): string
    {
        return $this->rootDirectory() . $this->relativePath();
    }

    /**
     * Normally this object should not be responsible of knowing its root directory
     * but keep it simple for now.
     * 
     * @return string
     *
     * @throws \Exception
     */
    private function rootDirectory(): string
    {
        $rootDirectory = sprintf(
            '%s%s..%s..%s..%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
        
        return $rootDirectory;
    }

    /**
     * Return the real path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function __toString(): string
    {
        $path = $this->absolutePath();

        return $path;
    }
}
