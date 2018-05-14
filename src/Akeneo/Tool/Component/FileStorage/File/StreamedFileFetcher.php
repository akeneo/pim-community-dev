<?php

namespace Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Fetch the raw file of a file stored in a virtual filesystem into a streamed response.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StreamedFileFetcher implements FileFetcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch(FilesystemInterface $filesystem, $fileKey, array $options = [])
    {
        if (!$filesystem->has($fileKey)) {
            throw new FileNotFoundException($fileKey);
        }

        if (false === $stream = $filesystem->readStream($fileKey)) {
            throw new FileTransferException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileKey)
            );
        }

        $headers = isset($options['headers']) ? $options['headers'] : [];

        return new StreamedFileResponse($stream, 200, $headers);
    }
}
