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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

class RemoteXlsxFileReaderFactory implements XlsxFileReaderFactoryInterface
{
    private ?string $localFilePath = null;

    public function __construct(
        private CellsFormatter $cellsFormatter,
        private FilesystemProvider $filesystemProvider,
        private RowCleaner $rowCleaner,
    ) {
    }

    public function create(string $filePath): XlsxFileReaderInterface
    {
        $this->localFilePath = \sprintf(
            '%s%s%s',
            \sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            \basename($filePath),
        );

        $fileSystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        $remoteStream = $fileSystem->readStream($filePath);

        \file_put_contents($this->localFilePath, $remoteStream);
        \fclose($remoteStream);

        return new XlsxFileReader($this->localFilePath, $this->cellsFormatter, $this->rowCleaner);
    }

    public function __destruct()
    {
        if ($this->localFilePath && file_exists($this->localFilePath)) {
            \unlink($this->localFilePath);
        }
    }
}
