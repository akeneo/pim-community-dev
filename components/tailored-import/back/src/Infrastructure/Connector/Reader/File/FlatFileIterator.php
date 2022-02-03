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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FlatFileIterator implements FileIteratorInterface
{
    private ReaderInterface $fileReader;
    private SheetInterface $sheet;
    private IteratorInterface $rows;
    private array $headers;

    public function __construct(
        private string $fileType,
        private string $filePath,
        private array $fileStructure
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
        $this->rewindRowIteratorAtFirstProductLine();
    }

    public function current(): ?array
    {
        $productRow = $this->rows->current();

        if (!$this->valid() || null === $productRow || empty($productRow)) {
            $this->rewind();

            return null;
        }

        $firstProductColumn = $this->fileStructure['product_column'];

        return array_slice($productRow->toArray(), $firstProductColumn);
    }

    public function next(): void
    {
        $this->rows->next();
    }

    public function key(): mixed
    {
        return $this->rows->key();
    }

    public function valid(): bool
    {
        return $this->rows->valid();
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    private function openFile(): ReaderInterface
    {
        $fileInfo = new \SplFileInfo($this->filePath);
        if (!$fileInfo->isFile()) {
            throw new FileNotFoundException(sprintf('File "%s" could not be found', $this->filePath));
        }

        $fileReader = ReaderFactory::createFromType($this->fileType);
        $fileReader->setShouldFormatDates(true);
        $fileReader->open($this->filePath);

        return $fileReader;
    }

    private function selectSheet(): SheetInterface
    {
        $sheetIterator = $this->fileReader->getSheetIterator();

        $sheetIterator->rewind();

        $sheetIndex = $this->fileStructure['sheet'];
        while ($sheetIndex !== $sheetIterator->key()) {
            $sheetIterator->next();
        }

        return $sheetIterator->current();
    }

    private function readHeaders(): array
    {
        $rowIterator = $this->sheet->getRowIterator();

        $rowIterator->rewind();

        $headersRowIndex = $this->fileStructure['headers_line'];
        while ($headersRowIndex !== $rowIterator->key()) {
            $rowIterator->next();
        }

        $headersRow = $rowIterator->current();
        $firstHeaderColumn = $this->fileStructure['headers_column'];
        $headerCells = array_slice($headersRow->getCells(), $firstHeaderColumn);

        return array_map(static fn (Cell $headerCell, int $relativeIndex) => [
            'index' => $firstHeaderColumn + $relativeIndex,
            'label' => $headerCell->getValue(),
        ], $headerCells);
    }

    private function rewindRowIteratorAtFirstProductLine(): void
    {
        $this->rows->rewind();

        $firstProductLine = $this->fileStructure['product_line'];
        while ($firstProductLine !== $this->rows->key()) {
            $this->rows->next();
        }
    }
}
