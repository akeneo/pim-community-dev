<?php

namespace Pim\Bundle\BaseConnectorBundle\Filesystem;

use Gaufrette\Filesystem;
use Gaufrette\Adapter;

/**
 * Factory of Gaufrette Filesystem configured with the Zip adapter
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
     * @param string $path
     *
     * @return Filesystem
     */
    public function createZip($path)
    {
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        return new Filesystem(new Adapter\Zip($path));
    }
}
