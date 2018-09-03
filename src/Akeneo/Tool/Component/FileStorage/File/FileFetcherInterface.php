<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemInterface;

/**
 * Fetch the raw file of a file stored in a virtual filesystem
 * into the local filesystem.
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
     * @param array               $options
     *
     * @throws FileTransferException
     * @throws \LogicException
     *
     * @return \SplFileInfo
     */
    public function fetch(FilesystemInterface $filesystem, $fileKey, array $options = []);
}
