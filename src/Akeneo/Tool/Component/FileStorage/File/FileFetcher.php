<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    /** @var string */
    protected $tmpDir;

    public function __construct(string $tmpDir)
    {
        $this->tmpDir = $tmpDir;
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

        $fsTools = new Filesystem();

        if (!$fsTools->exists($this->tmpDir . DIRECTORY_SEPARATOR. dirname($fileKey))) {
            $fsTools->mkdir($this->tmpDir . DIRECTORY_SEPARATOR . dirname($fileKey));
        }

        $localPathname = $this->tmpDir . DIRECTORY_SEPARATOR . $fileKey;

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
