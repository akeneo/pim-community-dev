<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MultiSelectTranslator implements FlatAttributeValueTranslatorInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::OPTION_MULTI_SELECT;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $optionKeys = $this->generateOptionKeys($values, $attributeCode);
        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            $optionKeys
        );

        $attributeOptionTranslations = array_change_key_case($attributeOptionTranslations, CASE_LOWER);

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (null === $value || '' === $value) {
                $result[$valueIndex] = $value;
                continue;
            }

            $optionCodes = explode(',', $value);
            $labelizedOptions = array_map(
                function ($optionCode) use ($attributeCode, $locale, $attributeOptionTranslations) {
                    $optionKey = self::generateOptionKey($attributeCode, $optionCode);

                    return $attributeOptionTranslations[$optionKey][$locale] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $optionCode);
                },
                $optionCodes
            );

            $result[$valueIndex] = implode(',', $labelizedOptions);
        }

        return $result;
    }

    private function generateOptionKeys(array $values, string $attributeCode): array
    {
        $optionKeys = [];
        foreach ($values as $value) {
            if (null === $value || '' === $value) {
                continue;
            }
            $optionCodes = explode(',', $value);
            $currentOptionKeys = array_map(
                static fn ($optionCode) => self::generateOptionKey($attributeCode, $optionCode),
                $optionCodes
            );

            $optionKeys = array_merge($optionKeys, $currentOptionKeys);
        }

        return $optionKeys;
    }

    private static function generateOptionKey(string $attributeCode, string $optionCode): string
    {
        return sprintf('%s.%s', strtolower($attributeCode), strtolower($optionCode));
    }
}
