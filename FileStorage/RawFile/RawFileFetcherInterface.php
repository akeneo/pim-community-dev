<?php

namespace Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\Model\FileInterface;
use League\Flysystem\FilesystemInterface;

/**
 * Fetch the raw file of a file stored in a virtual filesystem
 * into the temporary directory of the local filesystem.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RawFileFetcherInterface
{
    /**
     * @param FileInterface       $file
     * @param FilesystemInterface $filesystem
     *
     * @throws FileTransferException
     * @throws \LogicException
     *
     * @return \SplFileInfo
     *
     */
    public function fetch(FileInterface $file, FilesystemInterface $filesystem);
}
