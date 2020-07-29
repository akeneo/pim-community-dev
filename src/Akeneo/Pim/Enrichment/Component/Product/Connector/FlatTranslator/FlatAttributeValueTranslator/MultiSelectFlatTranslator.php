<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatAttributeValueTranslator;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;

class MultiSelectFlatTranslator implements FlatAttributeValueTranslatorInterface
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
        return $attributeType === AttributeTypes::OPTION_MULTI_SELECT;
    }

    public function translateValues(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $optionKeys = $this->extractOptionCodes($values, $attributeCode);
        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            $optionKeys
        );

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $optionCodes = explode(',', $value);
            $labelizedOptions = array_map(function ($optionCode) use ($attributeCode, $locale, $attributeOptionTranslations) {
                $optionKey = sprintf('%s.%s', $attributeCode, $optionCode);

                return $attributeOptionTranslations[$optionKey][$locale] ?? sprintf('[%s]', $optionCode);
            }, $optionCodes);

            $result[$valueIndex] = implode(',', $labelizedOptions);
        }

        return $result;
    }

    private function extractOptionCodes(array $values, string $attributeCode): array
    {
        $optionKeys = [];
        foreach ($values as $value) {
            $optionCodes = explode(',', $value);
            $currentOptionKeys = array_map(function ($optionCode) use ($attributeCode) {
                return sprintf('%s.%s', $attributeCode, $optionCode);
            }, $optionCodes);

            $optionKeys = array_merge($optionKeys, $currentOptionKeys);
        }

        return $optionKeys;
    }
}
