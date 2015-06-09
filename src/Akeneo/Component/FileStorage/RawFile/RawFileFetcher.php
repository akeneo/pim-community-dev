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
class RawFileFetcher implements RawFileFetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch(FileInterface $file, FilesystemInterface $filesystem)
    {
        if (!$filesystem->has($file->getKey())) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $file->getKey()));
        }

        $localPathname = tempnam(sys_get_temp_dir(), 'raw_file_fetcher_');

        if (false === $stream = $filesystem->readStream($file->getKey())) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $file->getKey())
            );
        }

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $file->getKey())
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
