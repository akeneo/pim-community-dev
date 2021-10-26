<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Webmozart\Assert\Assert;

class TableValuesProcessor implements ItemProcessorInterface
{
    public function process($item): array
    {
        /** @var TableRow $item */
        Assert::isInstanceOf($item, TableRow::class);

        return $this->toArray($item);
    }

    public function toArray(TableRow $tableRow): array
    {
        $attribute = \implode('-', \array_filter([
            $tableRow->attributeCode,
            $tableRow->localeCode,
            $tableRow->scopeCode,
        ]));

        $data = [
            'product' => $tableRow->entityId,
            'attribute' => $attribute,
        ];
        foreach ($tableRow->row as $columnId => $cell) {
            $id = ColumnId::fromString($columnId);
            $data[$id->extractColumnCode()->asString()] = $this->getStringValue($cell);
        }

        return $data;
    }

    protected function getStringValue(Cell $cell): string
    {
        $normalizedCell = $cell->normalize();
        if (\is_bool($normalizedCell)) {
            return $normalizedCell ? '1' : '0';
        }

        return (string) $normalizedCell;
    }
}
