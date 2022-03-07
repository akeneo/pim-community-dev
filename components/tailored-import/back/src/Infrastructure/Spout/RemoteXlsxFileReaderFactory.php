<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Spout;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use League\Flysystem\FilesystemReader;

class RemoteXlsxFileReaderFactory implements XlsxFileReaderFactoryInterface
{
    private ?string $localFilePath = null;

    public function __construct(
        private CellsFormatter $cellsFormatter,
        private FilesystemReader $filesystemReader
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

        $remoteStream = $this->filesystemReader->readStream($filePath);

        \file_put_contents($this->localFilePath, $remoteStream);
        \fclose($remoteStream);

        return new XlsxFileReader($this->localFilePath, $this->cellsFormatter);
    }

    public function __destruct()
    {
        if ($this->localFilePath) {
            \unlink($this->localFilePath);
        }
    }
}
