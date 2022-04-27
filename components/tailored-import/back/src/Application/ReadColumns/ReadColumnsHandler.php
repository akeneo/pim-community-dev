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

namespace Akeneo\Platform\TailoredImport\Application\ReadColumns;

use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;

class ReadColumnsHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $fileReaderFactory,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function handle(ReadColumnsQuery $query): ColumnCollection
    {
        $fileStructure = $query->getFileStructure();
        $fileReader = $this->fileReaderFactory->create($query->getFileKey());
        $headerRow = $fileReader->readRow($fileStructure->getSheetName(), $fileStructure->getHeaderLine());
        $headerRow = $this->truncateHeaderToFirstColumn($headerRow, $fileStructure->getFirstColumn());

        return ColumnCollection::create(array_map(
            fn ($index, $headerCell) => $this->fileHeaderToColumn($index, $headerCell),
            array_keys($headerRow), $headerRow,
        ));
    }

    private function fileHeaderToColumn(int $index, string $headerCell): Column
    {
        return Column::create(
            $this->uuidGenerator->generate(),
            $index,
            $headerCell,
        );
    }

    private function truncateHeaderToFirstColumn(array $headerRow, int $firstColumn): array
    {
        return array_slice($headerRow, $firstColumn);
    }
}
