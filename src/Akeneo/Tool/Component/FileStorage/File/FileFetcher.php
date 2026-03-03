<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
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
    /**
     * {@inheritdoc}
     */
    public function fetch(FilesystemReader $filesystem, $fileKey, array $options = [])
    {
        if (!$filesystem->fileExists($fileKey)) {
            throw new \LogicException(sprintf('The file "%s" is not present on the filesystem.', $fileKey));
        }

        try {
            $stream = $filesystem->readStream($fileKey);
        } catch (UnableToReadFile $e) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey),
                0,
                $e
            );
        }

        // TODO: replace this with a proper abstraction
        $fsTools = new Filesystem();
        $tmpDir = sys_get_temp_dir();

        if (!$fsTools->exists($tmpDir . DIRECTORY_SEPARATOR. dirname($fileKey))) {
            $fsTools->mkdir($tmpDir . DIRECTORY_SEPARATOR . dirname($fileKey));
        }

        $localPathname = $tmpDir . DIRECTORY_SEPARATOR . $fileKey;

        if (false === file_put_contents($localPathname, $stream)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
