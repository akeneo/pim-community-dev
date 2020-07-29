<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class MetricUnitFlatTranslator implements FlatAttributeValueTranslatorInterface
{
    public function support(string $attributeType, string $columnName): bool
    {
        $endWithUnit = substr_compare($columnName, '-unit', -strlen('-unit')) === 0;

        return $attributeType === AttributeTypes::METRIC && $endWithUnit;
    }

    /** TODO (not done in the first POC) */
    public function translateValues(string $attributeCode, array $properties, array $values, string $locale): array
    {
        return $values;
    }
}
