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
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\RowIterator;
use OpenSpout\Reader\XLSX\Sheet;

class XlsxFileIterator implements FileIteratorInterface
{
    private Reader $fileReader;
    private Sheet $sheet;
    private RowIterator $rows;
    private FileHeaderCollection $headers;

    public function __construct(
        private string $filePath,
        private FileStructure $fileStructure,
        private CellsFormatter $cellsFormatter,
        private RowCleaner $rowCleaner,
    ) {
        $this->fileReader = $this->openFile();
        $this->sheet = $this->selectSheet();
        $this->rows = $this->sheet->getRowIterator();
        $this->headers = $this->readHeaders();
    }

    public function __destruct()
    {
        $this->fileReader->close();
    }

    public function rewind(): void
    {
        $this->rewindRowIteratorOnFirstProductLine();
    }

    public function current(): ?array
    {
        $productRow = $this->rows->current();
        if (!$this->valid() || $productRow->isEmpty()) {
            $this->rewind();

            return null;
        }

        $firstColumn = $this->fileStructure->getFirstColumn();

        $row = array_values(array_slice($productRow->toArray(), $firstColumn));
        $row = $this->cellsFormatter->formatCells($row);
        $row = $this->rowCleaner->removeTrailingEmptyColumns($row);

        return $this->rowCleaner->padRowToLength($row, $this->headers->count());
    }

    public function next(): void
    {
        $this->rows->next();
        if (!$this->rows->valid()) {
            return;
        }

        if (empty(array_filter($this->rows->current()->toArray()))) {
            $this->next();
        }
    }

    public function key(): int
    {
        return $this->rows->key() - $this->fileStructure->getProductLine() + 1;
    }

    public function valid(): bool
    {
        return $this->rows->valid();
    }

    public function getHeaders(): FileHeaderCollection
    {
        return $this->headers;
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

    private function selectSheet(): Sheet
    {
        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        $sheetName = $this->fileStructure->getSheetName();

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

    private function readHeaders(): FileHeaderCollection
    {
        $rowIterator = $this->sheet->getRowIterator();
        $rowIterator->rewind();

        $headersRowIndex = $this->fileStructure->getHeaderLine();
        while ($headersRowIndex !== $rowIterator->key()) {
            $rowIterator->next();
        }

        $headersRow = $rowIterator->current();
        $firstColumn = $this->fileStructure->getFirstColumn();
        $headerValues = $this->cellsFormatter->formatCells($headersRow->toArray());
        $headerValues = array_values(array_slice($headerValues, $firstColumn));
        $headerValues = $this->rowCleaner->removeTrailingEmptyColumns($headerValues);

        // /!\ Index is relative => 0 is the first header column but not necessary the first file column
        // We have to homogenize this index generation with the column list generation from a file (RAB-494)
        $normalizedHeaders = array_map(fn (string $headerValue, int $relativeIndex) => [
            'index' => $firstColumn + $relativeIndex,
            'label' => $headerValue,
        ], array_values($headerValues), array_keys($headerValues));

        return FileHeaderCollection::createFromNormalized($normalizedHeaders);
    }

    private function rewindRowIteratorOnFirstProductLine(): void
    {
        $firstProductLine = $this->fileStructure->getProductLine();
        foreach ($this->rows as $index => $row) {
            if ($index === $firstProductLine) {
                return;
            }
        }
    }
}
