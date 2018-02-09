<?php

namespace Akeneo\Test\IntegrationTestsBundle;

/**
 * Object that represent a directory path.
 *
 * For instance new (Path('foo', 'bar')); will return /application/root/directory/foo/bar
 */
class Path
{
    /** @var array */
    private $directories;

    public function __construct(...$directories)
    {
        $this->directories = $directories;
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
        $path = implode(DIRECTORY_SEPARATOR, $this->directories);
        $absolutePath = sprintf(
            '%s%s%s',
            $this->getRootDirectory(),
            DIRECTORY_SEPARATOR,
            $path
        );

        if (false === $realAbsolutePath = realpath($absolutePath)) {
            throw new \Exception(sprintf('The directory "%s" is not a valid directory', $absolutePath));
        }

        return $realAbsolutePath;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function getRootDirectory(): string
    {
        $rootDirectory = sprintf(
            '%s%s..%s..%s..%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        if (false === $realRootDirectory = realpath($rootDirectory)) {
            throw new \Exception(sprintf('The root directory "%s" is not a valid directory', $rootDirectory));
        }

        return $realRootDirectory;
    }
}