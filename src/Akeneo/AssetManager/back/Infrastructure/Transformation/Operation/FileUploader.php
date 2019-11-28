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

namespace Akeneo\AssetManager\Infrastructure\Transformation\Operation;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Uploads a file to filesystem
 */
class FileUploader
{
    /** @var FilesystemProvider */
    private $filesystemProvider;

    public function __construct(FilesystemProvider $filesystemProvider)
    {
        $this->filesystemProvider = $filesystemProvider;
    }

    public function put(File $file, string $path): void
    {
        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);

        $uploadedFile = $filesystem->put($path, file_get_contents($file->getPathname()));
        if (false === $uploadedFile) {
            throw new \RuntimeException(sprintf('The file can not be uploaded to %s.', $path));
        }
    }
}
