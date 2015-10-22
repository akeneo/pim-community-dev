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
class FileFetcher implements FileFetcherInterface
{
    /** @var FilesystemInterface */
    protected $tmpFilesystem;

    /**
     * @param FilesystemInterface $tmpFilesystem
     */
    public function __construct(FilesystemInterface $tmpFilesystem)
    {
        $this->tmpFilesystem = $tmpFilesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(FilesystemInterface $filesystem, $fileKey)
    {
        if (!$filesystem->has($fileKey)) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $fileKey));
        }

        if (false === $stream = $filesystem->readStream($fileKey)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        if (!$this->tmpFilesystem->has(dirname($fileKey))) {
            $this->tmpFilesystem->createDir(dirname($fileKey));
        }

        $localPathname = $this->tmpFilesystem->getAdapter()->getPathPrefix() . $fileKey;

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
