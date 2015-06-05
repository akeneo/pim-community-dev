<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileStorage\RawFile;

use Akeneo\Component\FileStorage\Model\FileInterface;
use League\Flysystem\FilesystemInterface;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;

/**
 * Fetch the raw file of a file stored in a virtual filesystem
 * into the temporary directory of the local filesystem.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface RawFileFetcherInterface
{
    /**
     * @param FileInterface       $file
     * @param FilesystemInterface $filesystem
     *
     * @return \SplFileInfo
     *
     * @throws FileTransferException
     * @throws \LogicException
     */
    public function fetch(FileInterface $file, FilesystemInterface $filesystem);
}
