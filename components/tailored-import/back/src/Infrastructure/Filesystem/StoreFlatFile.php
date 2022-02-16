<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Filesystem;

use Akeneo\Platform\TailoredImport\Domain\Filesystem\FlatFileInfo;
use Akeneo\Platform\TailoredImport\Domain\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Domain\Filesystem\StoreFlatFileInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

class StoreFlatFile implements StoreFlatFileInterface
{
    public function __construct(
        private FileInfoRepositoryInterface $fileInfoRepository,
        private FileStorer $fileStorer,
    ) {
    }

    public function store(string $filePath, string $originalFilename): FlatFileInfo
    {
        $hash = sha1_file($filePath);
        $file = $this->fileInfoRepository->findOneBy([
            'hash' => $hash,
            'originalFilename' => $originalFilename,
            'storage' => Storage::FILE_STORAGE_ALIAS,
        ]);

        if (null === $file) {
            $uploadedFile = new \SplFileInfo($filePath);
            $file = $this->fileStorer->store($uploadedFile, Storage::FILE_STORAGE_ALIAS);
        }

        return new FlatFileInfo($file->getKey());
    }
}
