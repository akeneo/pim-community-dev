<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;

class SimpleSelectFlatTranslator implements AttributeFlatTranslator
{
    /**
     * @var GetExistingAttributeOptionsWithValues
     */
    private $getExistingAttributeOptionsWithValues;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function support(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::OPTION_SIMPLE_SELECT;
    }

    public function translateValues(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $optionKeys = array_map(function ($value) use ($attributeCode) {
            return sprintf('%s.%s', $attributeCode, $value);
        }, $values);

        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            $optionKeys
        );

        $result = [];
        foreach ($values as $valueIndex => $value) {
            $optionKey = sprintf('%s.%s', $attributeCode, $value);
            $attributeOptionTranslation = $attributeOptionTranslations[$optionKey][$locale] ?? sprintf('[%s]', $value);
            $result[$valueIndex] = $attributeOptionTranslation;
        }

        return $result;
    }
}
