<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Fetch the raw file of a file stored in a virtual filesystem into the local filesystem.
 * When the file is fetched, the file path and/or the filename can be different on the local filesystem
 * with options "filePath" and "filename"
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OutputFileFetcher implements FileFetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch(FilesystemReader $filesystem, $fileKey, array $options = [])
    {
        if (!isset($options['filePath']) || '' === $options['filePath']) {
            throw new \InvalidArgumentException('Options "filePath" has to be filled');
        }

        if (!$filesystem->fileExists($fileKey)) {
            throw new FileNotFoundException($fileKey);
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

        $filePath = DIRECTORY_SEPARATOR !== substr($options['filePath'], -1)
            ? $options['filePath'] . DIRECTORY_SEPARATOR : $options['filePath'];

        $filename = !isset($options['filename']) || '' === $options['filename']
            ? basename($fileKey) : $options['filename'];

        $localPathname = $filePath . $filename;
        // TODO: replace this with a filesystem abstraction
        $localFilesystem = new Filesystem();

        try {
            $localFilesystem->dumpFile($localPathname, $stream);
        } catch (IOException $e) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey),
                $e->getCode(),
                $e
            );
        }

        return new \SplFileInfo($localPathname);
    }
}
