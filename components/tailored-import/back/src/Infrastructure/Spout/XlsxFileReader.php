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
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderInterface;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\XLSX\Reader;

class XlsxFileReader implements XlsxFileReaderInterface
{
    private ReaderInterface $fileReader;

    public function __construct(
        private string $filePath,
        private CellsFormatter $cellsFormatter,
    ) {
        $this->fileReader = $this->openFile();
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

    public function readRows(?string $sheetName, int $start, ?int $length = null): array
    {
        $rows = [];
        $sheet = $this->selectSheet($sheetName);
        $rowIterator = $sheet->getRowIterator();

        foreach ($rowIterator as $index => $row) {
            if ($index >= $start) {
                $rows[] = $this->cellsFormatter->formatCells($row->toArray());
            }

            if (null !== $length && $index + 1 === $start + $length) {
                break;
            }
        }

        $rows = $this->removeTrailingEmptyRows($rows);

        return $this->padRowsToTheLongestRow($rows);
    }

    public function readColumnValues(?string $sheetName, int $productLine, array $columnIndices): array
    {
        $rows = $this->readRows($sheetName, $productLine);

        $rowsByColumnIndex = [];
        foreach ($rows as $row) {
            foreach ($columnIndices as $columnIndex) {
                $rowsByColumnIndex[$columnIndex][] = $row[$columnIndex];
            }
        }

        return $rowsByColumnIndex;
    }

    public function getSheetNames(): array
    {
        $sheetList = [];

        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        foreach ($sheetIterator as $sheet) {
            $sheetList[] = $sheet->getName();
        }

        return $sheetList;
    }


    private function selectSheet(?string $sheetName): SheetInterface
    {
        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        if ($sheetName === null) {
            return $sheetIterator->current();
        }

        foreach ($sheetIterator as $sheet) {
            if ($sheet->getName() === $sheetName) {
                return $sheet;
            }
        }

        throw new SheetNotFoundException($sheetName);
    }

    private function padRowsToTheLongestRow(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $maxCellPerRow = count(max($rows));

        return array_map(
            static fn (array $row) => array_pad($row, $maxCellPerRow, ''),
            $rows,
        );
    }

    private function removeTrailingEmptyRows(array $rows): array
    {
        $reversedRows = array_reverse($rows);
        foreach ($reversedRows as $index => $row) {
            if (!empty(array_filter($row))) {
                break;
            }

            unset($reversedRows[$index]);
        }

        return array_reverse($reversedRows);
    }
}
