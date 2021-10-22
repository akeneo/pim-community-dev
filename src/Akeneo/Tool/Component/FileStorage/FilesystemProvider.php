<?php

namespace Akeneo\Tool\Component\FileStorage;

use League\Flysystem\FilesystemOperator;

/**
 * Resolves a filesystem registered in the MountManager.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilesystemProvider
{
    private array $mountedFilesystems = [];

    public function __construct(iterable $filesystems)
    {
        foreach ($filesystems as $key => $filesystem) {
            $this->mountedFilesystems[$key] = $filesystem;
        }
    }

    public function getFilesystem($name): FilesystemOperator
    {
        if (!isset($this->mountedFilesystems[$name])) {
            throw new \LogicException(\sprintf('Could not find the %s filesystem', $name));
        }

        return $this->mountedFilesystems[$name];
    }
}
