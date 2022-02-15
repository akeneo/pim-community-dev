<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Platform\TailoredImport\Domain\Model\DataMapping;
use Akeneo\Platform\TailoredImport\Domain\Model\Row;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteDataMappingHandler
{
    public function handle(ExecuteDataMappingQuery $executeDataMappingQuery): UpsertProductCommand
    {
        /** @var array<ValueUserIntent> $valueUserIntents */
        $valueUserIntents = [];

        $dataMappingCollection = $executeDataMappingQuery->getDataMappingCollection();
        $identifierAttributeCode = $this->getIdentifierAttributeCode();
        $productIdentifier = null;

        /** @var DataMapping $dataMapping */
        foreach ($dataMappingCollection->getIterator() as $dataMapping) {
            $target = $dataMapping->getTarget();

            /**
             * How do we structure the code to determine the type of target property OR attribute,
             *  - Attribute: deal with action type set OR add, and ValueUserIntent based on primitive type of the cellData value
             *  - Property:  Determine the correct user intent
             */
            $cellData = $this->mergeCellData($executeDataMappingQuery->getRow(), $dataMapping->getSources());
            /** TODO Iterate over operation */
            if ($target instanceof TargetAttribute) {
                if ($identifierAttributeCode === $target->getCode()) {
                    $productIdentifier = $cellData;
                } else {
                    $valueUserIntents[] = new SetTextValue(
                        $target->getCode(),
                        $target->getLocale(),
                        $target->getChannel(),
                        $cellData,
                    );
                }
            }
        }

        return new UpsertProductCommand(
            userId: 1,
            productIdentifier: $productIdentifier,
            valuesUserIntent: $valueUserIntents,
        );
    }

    private function mergeCellData(Row $row, array $sources): string
    {
        return implode('', array_map(
            static fn (string $uuid) => $row->getCellData($uuid),
            $sources,
        ));
    }

    // TODO: use the upcoming get by attribute type public api query
    private function getIdentifierAttributeCode(): string
    {
        return 'sku';
    }
}
