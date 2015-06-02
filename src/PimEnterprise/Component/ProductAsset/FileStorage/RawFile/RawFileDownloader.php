<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use League\Flysystem\FilesystemInterface;
use PimEnterprise\Component\ProductAsset\Exception\FileTransferException;
use PimEnterprise\Component\ProductAsset\Model\FileInterface;

/**
 * Download the raw file of a file stored in a virtual filesystem
 * into the temporary directory of the local filesystem.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class RawFileDownloader implements RawFileDownloaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function download(FileInterface $file, FilesystemInterface $filesystem, $tmpDirectory = 'download/')
    {
        if (!$filesystem->has($file->getKey())) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $file->getKey()));
        }

        $tmpDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpDirectory;
        if (!is_dir($tmpDirectory)) {
            mkdir($tmpDirectory);
        }

        $localPathname = $tmpDirectory . uniqid();

        if (false === $stream = $filesystem->readStream($file->getKey())) {
            throw new FileTransferException(
                sprintf('Unable to download the file "%s" from the filesystem.', $file->getKey())
            );
        }

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to download the file "%s" from the filesystem.', $file->getKey())
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
