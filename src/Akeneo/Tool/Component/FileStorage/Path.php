<?php

namespace Akeneo\Tool\Component\FileStorage;

/**
 * Represent a file or a directory path.
 *
 * Example: to build this path: `local://path/to/file.png` you can use `new Path('local, 'path', 'to', 'file.png');`
 * Example: to build this path: `path/to/file.png` you can use `Path::withoutStorage('path', 'to', 'file.png');`
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Path
{
    /** @var array */
    private $pathFragment;

    /**  @var string */
    private $storage;

    public function __construct(string $storage, string ...$pathFragment)
    {
        $this->pathFragment = $pathFragment;
        $this->storage = $storage;
    }

    public static function withoutStorage(string ...$directories): self
    {
        return new self('', ...$directories);
    }

    public function __toString(): string
    {
        $path = array_reduce($this->pathFragment, function ($path, $fragment) {
            $fragment = trim($fragment, "/");

            if ('' === $path) {
                return $fragment;
            }

            return $path.DIRECTORY_SEPARATOR.$fragment;
        }, '');

        if ('' !== $this->storage) {
            return $this->storage.'://'.$path;
        }

        return $path;
    }
}
