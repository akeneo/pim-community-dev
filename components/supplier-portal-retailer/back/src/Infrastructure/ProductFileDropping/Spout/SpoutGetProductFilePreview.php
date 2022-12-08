<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Spout;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePreview;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePreview;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\Sheet;
use Psr\Log\LoggerInterface;

final class SpoutGetProductFilePreview implements GetProductFilePreview
{
    private const MAX_ROWS = 20;
    private const MAX_COLUMN_PER_ROWS = 100;

    public function __construct(
        private readonly SpoutRemoteXlsxFileReaderFactory $spoutRemoteXlsxFileReaderFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $productFilePath, string $productFileName): ProductFilePreview
    {
        try {
            $xlsxReader = $this->spoutRemoteXlsxFileReaderFactory->create($productFilePath, $productFileName);
        } catch (\Exception $e) {
            $this->logger->error('An error occured while trying to preview a product file.', [
                'data' =>
                    [
                        'path' => $productFilePath,
                        'error' => $e->getMessage(),
                    ],
            ]);

            throw new UnableToReadProductFile(previous: $e);
        }

        $firstSheet = $this->getFirstSheet($xlsxReader);
        $firstRows = $this->getFirstRows($firstSheet);
        $firstRows = $this->padRowsToTheLongestRow($firstRows);

        return new ProductFilePreview($firstRows);
    }

    private function getFirstSheet(Reader $xlsxReader): Sheet
    {
        foreach ($xlsxReader->getSheetIterator() as $sheet) {
            if (0 === $sheet->getIndex()) {
                return $sheet;
            }
        }

        throw new \Exception('Cannot read first sheet');
    }

    private function getFirstRows(Sheet $sheet): array
    {
        $firstRowsIterator = new \LimitIterator($sheet->getRowIterator(), 0, self::MAX_ROWS);

        return array_map(
            fn (Row $row) => $this->getRowFirstCells($row),
            iterator_to_array($firstRowsIterator),
        );
    }

    private function getRowFirstCells(Row $row): array
    {
        $firstCells = new \LimitIterator(new \ArrayIterator($row->getCells()), 0, self::MAX_COLUMN_PER_ROWS);

        return array_map(
            fn (Cell $cell) => $cell->getValue(),
            iterator_to_array($firstCells),
        );
    }

    private function padRowsToTheLongestRow(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $maxCellPerRow = count(max($rows));

        return array_map(fn (array $row) => array_pad($row, $maxCellPerRow, ''), $rows);
    }
}
