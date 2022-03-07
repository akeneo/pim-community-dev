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

use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;

class FileIteratorFactory
{
    const FILE_TYPE_XLSX = "xlsx";

    public function __construct(
        private CellsFormatter $cellsFormatter,
    ) {
    }

    public function create(string $fileType, string $filePath, FileStructure $fileStructure): FileIteratorInterface
    {
        return match ($fileType) {
            self::FILE_TYPE_XLSX => new XlsxFileIterator($filePath, $fileStructure, $this->cellsFormatter),
            default => throw new \InvalidArgumentException(sprintf('Unsupported file type "%s"', $fileType)),
        };
    }
}
