<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;

/**
 * Fetch files from the virtual storage filesystem to the local export filesystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileExporterInterface
{
    /**
     * @param string $key
     * @param string $localPathname
     * @param string $storageAlias
     *
     * @throws \LogicException
     * @throws FileTransferException
     *
     * @return bool
     */
    public function export($key, $localPathname, $storageAlias);
}
