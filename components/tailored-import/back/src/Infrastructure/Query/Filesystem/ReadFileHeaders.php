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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query\Filesystem;

use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\ReadFileHeadersInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FileIteratorFactory;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class ReadFileHeaders implements ReadFileHeadersInterface
{
    public function __construct(
        private FilesystemProvider $filesystemProvider,
        private FileIteratorFactory $flatFileIteratorFactory,
    ) {
    }

    public function read(string $fileKey, FileStructure $fileStructure): FileHeaderCollection
    {
        $localFilePath = \sprintf(
            '%s%s%s',
            \sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            \basename($fileKey),
        );
        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $remoteStream = $filesystem->readStream($fileKey);

        \file_put_contents($localFilePath, $remoteStream);
        \fclose($remoteStream);

        $fileIterator = $this->flatFileIteratorFactory->create('xlsx', $localFilePath, $fileStructure);

        \unlink($localFilePath);

        $fileHeaders = $fileIterator->getHeaders();

        return $fileHeaders;
    }
}
