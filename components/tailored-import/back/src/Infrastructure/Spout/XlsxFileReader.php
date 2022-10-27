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
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\Sheet;

class XlsxFileReader implements XlsxFileReaderInterface
{
    private Reader $fileReader;

    public function __construct(
        private string $filePath,
        private CellsFormatter $cellsFormatter,
        private RowCleaner $rowCleaner,
    ) {
        $this->fileReader = $this->openFile();
    }

    public function __destruct()
    {
        $this->fileReader->close();
    }

    private function openFile(): Reader
    {
        $fileInfo = new \SplFileInfo($this->filePath);
        if (!$fileInfo->isFile()) {
            throw new FileNotFoundException($this->filePath);
        }

        $fileReader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX, [
            'shouldFormatDates' => true,
            'shouldPreserveEmptyRows' => true,
        ]);

        $fileReader->open($this->filePath);

        return $fileReader;
    }

    public function readRow(?string $sheetName, int $lineNumber): array
    {
        $rows = $this->readRows($sheetName, $lineNumber, 1);

        return empty($rows) ? [] : current($rows);
    }

    public function readRows(?string $sheetName, int $start, int $length): array
    {
        $rows = [];
        $sheet = $this->selectSheet($sheetName);
        $rowIterator = $sheet->getRowIterator();

        foreach ($rowIterator as $index => $row) {
            if ($index >= $start) {
                $rows[] = $this->cellsFormatter->formatCells($row->toArray());
            }

            if ($index + 1 === $start + $length) {
                break;
            }
        }

        $rows = $this->removeTrailingEmptyRows($rows);
        $rows = $this->removeTrailingEmptyColumns($rows);

        return $this->padRowsToTheLongestRow($rows);
    }

    public function readColumnsValues(?string $sheetName, int $productLine, array $columnIndices, int $length): array
    {
        $rows = $this->readRows($sheetName, $productLine, $length);

        $rowsByColumnIndex = [];
        foreach ($columnIndices as $columnIndex) {
            $rowsByColumnIndex[$columnIndex] = \array_map(static fn (array $row) => $row[$columnIndex] ?? '', $rows);
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

    private function selectSheet(?string $sheetName): Sheet
    {
        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        if (null === $sheetName) {
            return $sheetIterator->current();
        }

        foreach ($sheetIterator as $sheet) {
            if ($sheet->getName() === $sheetName) {
                return $sheet;
            }
        }

        throw new SheetNotFoundException($sheetName);
    }

    /**
     * @param array<int, string[]> $rows
     */
    private function padRowsToTheLongestRow(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $maxCellPerRow = count(max($rows));

        return array_map(fn (array $row) => $this->rowCleaner->padRowToLength($row, $maxCellPerRow), $rows);
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

    private function removeTrailingEmptyColumns(array $rows): array
    {
        return array_map(fn (array $row) => $this->rowCleaner->removeTrailingEmptyColumns($row), $rows);
    }
}
