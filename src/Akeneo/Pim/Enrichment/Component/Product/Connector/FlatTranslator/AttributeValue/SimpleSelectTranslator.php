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
class SimpleSelectTranslator implements FlatAttributeValueTranslatorInterface
{
    private GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues;

    public function __construct(GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues)
    {
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        return $attributeType === AttributeTypes::OPTION_SIMPLE_SELECT;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $locale): array
    {
        $optionKeys = array_map(
            function ($value) use ($attributeCode) {
                return sprintf('%s.%s', $attributeCode, $value);
            },
            $values
        );

        $attributeOptionTranslations = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            $optionKeys
        );

        $result = [];
        foreach ($values as $valueIndex => $value) {
            if (null === $value || '' === $value) {
                $result[$valueIndex] = $value;
                continue;
            }

            $optionKey = sprintf('%s.%s', $attributeCode, $value);
            $attributeOptionTranslation = $attributeOptionTranslations[$optionKey][$locale] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
            $result[$valueIndex] = $attributeOptionTranslation;
        }

        return $result;
    }
}
