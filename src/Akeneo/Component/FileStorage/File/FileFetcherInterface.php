<?php

namespace Akeneo\Component\FileStorage\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemInterface;

/**
 * Fetch the raw file of a file stored in a virtual filesystem
 * into the temporary directory of the local filesystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileFetcherInterface
{
    /**
     * @param FilesystemInterface $filesystem
     * @param string              $fileKey
     *
     * @throws FileTransferException
     * @throws \LogicException
     *
     * @return \SplFileInfo
     */
    public function fetch(FilesystemInterface $filesystem, $fileKey);
}
