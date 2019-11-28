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

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\TemporaryFileFactory;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Downloads a file from filesystem and put it in the temporary folder
 */
class FileDownloader
{
    /** @var FilesystemProvider */
    private $filesystemProvider;

    /** @var TemporaryFileFactory */
    private $temporaryFileFactory;

    public function __construct(
        FilesystemProvider $filesystemProvider,
        TemporaryFileFactory $temporaryFileFactory
    ) {
        $this->filesystemProvider = $filesystemProvider;
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    public function get(string $path): File
    {
        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        if (!$filesystem->has($path)) {
            throw new MediaFileNotFoundException(sprintf('The file "%s" can not be found.', $path));
        }

        $fileContent = $filesystem->read($path);
        if (false === $fileContent) {
            throw new MediaFileNotFoundException(sprintf('The file "%s" can not be downloaded.', $path));
        }

        return $this->temporaryFileFactory->createFromContent($fileContent);
    }
}
