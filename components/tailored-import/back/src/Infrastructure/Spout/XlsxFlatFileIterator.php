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

use Akeneo\Platform\TailoredImport\Application\ReadFile\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Domain\Exception\FileNotFoundException;
use Akeneo\Platform\TailoredImport\Domain\Exception\SheetNotFoundException;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\XLSX\Reader;

class XlsxFlatFileIterator implements FlatFileIteratorInterface
{
    private ReaderInterface $fileReader;
    private SheetInterface $sheet;
    private IteratorInterface $rows;
    private FileHeaderCollection $headers;

    public function __construct(
        private string $filePath,
        private array $fileStructure,
        private CellsFormatter $cellsFormatter,
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
        if (!$this->valid() || null === $productRow || empty($productRow)) {
            $this->rewind();

            return null;
        }

        $firstColumn = $this->fileStructure['first_column'];

        $cells = array_values(array_slice($productRow->toArray(), $firstColumn));
        $formattedCells = $this->cellsFormatter->format($cells);

        return $this->addTrimmedCells($formattedCells);
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
        return $this->rows->key() - 1 - $this->fileStructure['product_line'];
    }

    public function valid(): bool
    {
        return $this->rows->valid();
    }

    public function getHeaders(): FileHeaderCollection
    {
        return $this->headers;
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

    private function selectSheet(): SheetInterface
    {
        $sheetIterator = $this->fileReader->getSheetIterator();
        $sheetIterator->rewind();

        $sheetName = $this->fileStructure['sheet_name'];
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

        $headersRowIndex = $this->fileStructure['header_line'];
        while (1 + $headersRowIndex !== $rowIterator->key()) { // Iterator keys starts from 1
            $rowIterator->next();
        }

        $headersRow = $rowIterator->current();
        $firstColumn = $this->fileStructure['first_column'];
        $headerCells = array_values(array_slice($headersRow->getCells(), $firstColumn));

        // /!\ Index is relative => 0 is the first header column but not necessary the first file column
        // We have to homogenize this index generation with the column list generation from a file (RAB-494)
        $normalizedHeaders = array_map(static fn (Cell $headerCell, int $relativeIndex) => [
            'index' => $firstColumn + $relativeIndex,
            'label' => $headerCell->getValue(),
        ], array_values($headerCells), array_keys($headerCells));

        return FileHeaderCollection::createFromNormalized($normalizedHeaders);
    }

    private function rewindRowIteratorOnFirstProductLine(): void
    {
        $firstProductLine = $this->fileStructure['product_line'];
        foreach ($this->rows as $index => $row) {
            if ($index - 1 === $firstProductLine) {
                return;
            }
        }
    }

    private function addTrimmedCells(array $formattedCells): array
    {
        $firstColumn = $this->fileStructure['first_column'];
        $expectedValueCount = $this->headers->count() - $firstColumn;

        return array_replace(array_fill(0, $expectedValueCount, ''), $formattedCells);
    }
}
