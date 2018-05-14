<?php

namespace Akeneo\Tool\Component\FileStorage;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

/**
 * Resolves a filesystem registered in the MountManager.
 *
 * It's a small wrapper to the \League\Flysystem\MountManager to allow the Akeneo and PIM
 * services to be totally independent/agnostic from Flysystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilesystemProvider
{
    /** @var MountManager */
    protected $mountManager;

    /**
     * @param MountManager $mountManager
     */
    public function __construct(MountManager $mountManager)
    {
        $this->mountManager = $mountManager;
    }

    /**
     * Get the filesystem with the corresponding name.
     *
     * @param string $name
     *
     * @throws \LogicException
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($name)
    {
        return $this->mountManager->getFilesystem($name);
    }
}
