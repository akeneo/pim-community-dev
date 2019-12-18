<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * Downloads a file from filesystem and put it in the temporary folder
 */
class FileDownloader
{
    /** @var FilesystemProvider */
    private $filesystemProvider;

    /** @var FileFetcherInterface */
    private $fileFetcher;

    public function __construct(FilesystemProvider $filesystemProvider, FileFetcherInterface $fileFetcher)
    {
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
    }

    public function get(string $key, string $destinationDir, string $originalFilename): string
    {
        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $file = $this->fileFetcher->fetch(
            $filesystem,
            $key,
            [
                'filePath' => $destinationDir,
                'filename' => $originalFilename,
            ]
        );

        return $file->getPathname();
    }
}
