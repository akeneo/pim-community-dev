<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\Filesystem;
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
    /** @var Filesystem */
    protected $tmpFilesystem;

    /** @var string */
    private $tmpStorageDir;

    /*
     * TODO: remove null for $tmpStorageDir
     */
    public function __construct(Filesystem $tmpFilesystem, string $tmpStorageDir = null)
    {
        $this->tmpFilesystem = $tmpFilesystem;
        $this->tmpStorageDir = $tmpStorageDir;
        if (null === $tmpStorageDir) {
            $this->tmpStorageDir = $this->tmpFilesystem->getAdapter()->getPathPrefix();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(FilesystemInterface $filesystem, $fileKey, array $options = [])
    {
        if (!$filesystem->has($fileKey)) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $fileKey));
        }

        if (false === $stream = $filesystem->readStream($fileKey)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        $prefix = $options['prefix'] ?? $this->tmpStorageDir;
        $localPathname = rtrim($prefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileKey;

        if (!is_dir(dirname($localPathname))) {
            mkdir(dirname($localPathname), 0777, true);
        }

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to put the file "%s" from the filesystem.', $localPathname)
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
