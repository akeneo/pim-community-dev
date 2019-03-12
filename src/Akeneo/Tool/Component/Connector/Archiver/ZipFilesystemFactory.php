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
     * @param string $absolutePath
     *
     * @return Filesystem
     */
    public function createZip($absolutePath)
    {
        if (!is_dir(dirname($absolutePath))) {
            throw new \InvalidArgumentException(sprintf('The provided path "%s" is not a valid directory', $absolutePath));
        }

        return new Filesystem(new WriteStreamZipArchiveAdapter($absolutePath));
    }
}
