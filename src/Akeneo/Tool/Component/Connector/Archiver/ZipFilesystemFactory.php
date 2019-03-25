<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use League\Flysystem\Filesystem;

/**
 * Factory of Flysystem Filesystem configured with the Zip adapter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ZipFilesystemFactory
{
    /**
     * Create a Zip filesystem configured with the given path
     *
     * @param Filesystem $filesystem
     * @param string     $zipName
     *
     * @return Filesystem
     */
    public function createZip(Filesystem $filesystem, string $zipName)
    {
        $relativePath = uniqid() . DIRECTORY_SEPARATOR . $zipName;

        if (!$filesystem->has(dirname($relativePath))) {
            $filesystem->createDir(dirname($relativePath));
        }

        $absolutePath = $filesystem->getAdapter()->getPathPrefix() . $relativePath;

        return new Filesystem(new WriteStreamZipArchiveAdapter($absolutePath));
    }
}
