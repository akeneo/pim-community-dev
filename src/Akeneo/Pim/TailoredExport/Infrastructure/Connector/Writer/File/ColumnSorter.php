<?php


namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;

class ColumnSorter implements ColumnSorterInterface
{
    /**
     * @param array<array> $columns
     * @param array<string> $context
     * @return array<array>
     */
    public function sort(array $columns, array $context = []): array
    {
        return $columns;
    }
}
