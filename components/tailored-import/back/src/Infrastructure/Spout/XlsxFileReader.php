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

use Akeneo\Platform\TailoredImport\Domain\Exception\FileNotFoundException;
use Akeneo\Platform\TailoredImport\Domain\Exception\SheetNotFoundException;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\XLSX\Reader;

class XlsxFileReader implements XlsxFileReaderInterface
{
    private ReaderInterface $fileReader;
    private SheetInterface $sheet;

    public function __construct(
        private string $filePath,
        private CellsFormatter $cellsFormatter,
    ) {
        $this->fileReader = $this->openFile();
        $this->selectSheet(null);
    }

    public function __destruct()
    {
        $this->fileReader->close();
    }

    private function openFile(): ReaderInterface
    {
        $fileInfo = new \SplFileInfo($this->filePath);
        if (!$fileInfo->isFile()) {
            throw new FileNotFoundException($this->filePath);
        }

        /** @var Reader $fileReader */
        $fileReader = ReaderFactory::createFromType('xlsx');
        $fileReader->setShouldPreserveEmptyRows(true);
        $fileReader->setShouldFormatDates(true);
        $fileReader->open($this->filePath);

        return $fileReader;
    }

    public function selectSheet(?string $sheetName): void
    {
        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        if (null === $sheetName) {
            $this->sheet = $sheetIterator->current();
            return;
        }

        foreach ($sheetIterator as $sheet) {
            if ($sheet->getName() === $sheetName) {
                $this->sheet = $sheet;
                return;
            }
        }

        throw new SheetNotFoundException($sheetName);
    }

    public function readLine(int $line): array
    {
        $rowIterator = $this->sheet->getRowIterator();
        foreach ($rowIterator as $index => $row) {
            if ($index === $line) {
                return $this->cellsFormatter->formatCells($row->toArray());
            }
        }

        return [];
    }

    public function getSheetList(): array
    {
        $sheetList = [];

        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        foreach ($sheetIterator as $sheet) {
            $sheetList[] = $sheet->getName();
        }

        return $sheetList;
    }
}
