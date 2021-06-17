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
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Downloads a file from the storage file system into the specified local directory, and returns a copy of the file
 * It caches up to 10 files at a time in order to avoid downloading the same file several times
 *
 * BEWARE: the cached files are not removed from the local filesystem, it is your responsibility to delete them afterwards
 */
class FileDownloader
{
    private const MAX_CACHED_FILES = 10;

    private FilesystemProvider $filesystemProvider;

    private FileFetcherInterface $fileFetcher;

    private Filesystem $filesystem;

    /** @var string[] */
    private ?array $cachedFiles = null;

    public function __construct(
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        Filesystem $filesystem
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->filesystem = $filesystem;
    }

    public function get(string $key, string $destinationDir, string $originalFilename): File
    {
        if (!isset($this->cachedFiles[$key])) {
            $this->cachedFiles[$key] = $this->download($key, $destinationDir)->getPathname();
            if (count($this->cachedFiles) > self::MAX_CACHED_FILES) {
                $oldFile = array_shift($this->cachedFiles);
                if ($this->filesystem->exists($oldFile)) {
                    $this->filesystem->remove($oldFile);
                }
            }
        }

        $newFilename = sprintf('%s%s%s', $destinationDir, DIRECTORY_SEPARATOR, $originalFilename);
        $this->filesystem->copy($this->cachedFiles[$key], $newFilename, true);

        return new File($newFilename, false);
    }

    private function download(string $key, string $destinationDir): \SplFileInfo
    {
        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        return $this->fileFetcher->fetch(
            $filesystem,
            $key,
            [
                'filePath' => $destinationDir,
                'filename' => Uuid::uuid4()->toString(),
            ]
        );
    }
}
