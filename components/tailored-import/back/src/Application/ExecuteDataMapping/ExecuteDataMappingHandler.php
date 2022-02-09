<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredImport\Application\Common\DataMapping;
use Akeneo\Platform\TailoredImport\Application\Common\DataMappingCollection;
use Akeneo\Platform\TailoredImport\Application\Common\TargetAttribute;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExecuteDataMappingHandler
{
    private const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    public function __construct(
        private GetAttributes $getAttributes
    ) {}

    public function handle(ExecuteDataMappingQuery $executeDataMappingQuery)
    {
        /** @var array<ValueUserIntent> $valueUserIntents */
        $valueUserIntents = [];

        /** @var DataMapping $dataMapping */
        foreach ($executeDataMappingQuery->getDataMappingCollection()->getIterator() as $dataMapping) {
            /**
             * How do we structure the code to determine the type of target property OR attribute,
             *  - Attribute: deal with action type set OR add, and ValueUserIntent based on primitive type of the cellData value
             *  - Property:  Determine the correct
             */
            $cellData = implode("", array_map(static fn(string $uuid) => $executeDataMappingQuery->getRow()->getCellData($uuid), $dataMapping->getSources()));
            /** TODO Iterate over operation */
            $target = $dataMapping->target();
            $valueUserIntents[] = new SetTextValue($target->code(), $target->locale(), $target->channel(), $cellData);
        }

        return new UpsertProductCommand(
            userId: 1,
            productIdentifier: $this->getIdentifierAttributeCode($executeDataMappingQuery->getDataMappingCollection()),
            valuesUserIntent: $valueUserIntents
        );
    }

    private function getIdentifierAttributeCode(DataMappingCollection $dataMappingCollection): string
    {
        $attributeTargetCodes = [];

        /** @var DataMapping $dataMapping */
        foreach ($dataMappingCollection->iterator() as $dataMapping) {
            if ($dataMapping->target() instanceof TargetAttribute) {
                $attributeTargetCodes[] = $dataMapping->target()->code();
            }
        }

        $targetedAttributes = $this->getAttributes->forCodes($attributeTargetCodes);
        /** @var Attribute $targetedIdentifierAttribute */
        $targetedIdentifierAttribute = current(array_filter($targetedAttributes, function (?Attribute $attribute) {
            return null !== $attribute && self::IDENTIFIER_ATTRIBUTE_TYPE === $attribute->type();
        }));

        return $targetedIdentifierAttribute->code();
    }
}