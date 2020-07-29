<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class MetricUnitFlatTranslator implements FlatAttributeValueTranslatorInterface
{
    public function supports(string $attributeType, string $columnName): bool
    {
        $endWithUnit = substr_compare($columnName, '-unit', -strlen('-unit')) === 0;

        return $attributeType === AttributeTypes::METRIC && $endWithUnit;
    }

    /** TODO (not done in the first POC) */
    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        return $values;
    }
}
