<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Webmozart\Assert\Assert;

class TableValuesProcessor implements ItemProcessorInterface
{
    private string $entityName;

    public function __construct(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritDoc}
     */
    public function process($tableRow): array
    {
        Assert::isInstanceOf($tableRow, TableRow::class);

        $attribute = \implode('-', \array_filter([
            $tableRow->attributeCode,
            $tableRow->localeCode,
            $tableRow->scopeCode,
        ]));

        $data = [
            $this->entityName => $tableRow->entityId,
            'attribute' => $attribute,
        ];
        foreach ($tableRow->row as $columnId => $cell) {
            $id = ColumnId::fromString($columnId);
            $data[$id->extractColumnCode()->asString()] = $cell->asString();
        }

        return $data;
    }
}
