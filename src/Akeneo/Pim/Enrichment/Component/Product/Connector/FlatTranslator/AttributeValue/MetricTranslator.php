<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;

class MetricTranslator implements FlatAttributeValueTranslatorInterface
{
    private const UNIT_SUFFIX = '-unit';

    private GetUnitTranslations $getUnitTranslations;

    public function __construct(GetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        $isEndingWithUnit = str_ends_with($columnName, self::UNIT_SUFFIX);

        return $attributeType === AttributeTypes::METRIC && $isEndingWithUnit;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $localeCode): array
    {
        if (!isset($properties['measurement_family_code'])) {
            throw new \LogicException(sprintf('Expected properties to have a measurement family code to translate metric attribute values'));
        }

        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $properties['measurement_family_code'],
            $localeCode
        );

        return array_map(function ($value) use ($unitTranslations) {
            if (empty($value)) {
                return $value;
            }

            return $unitTranslations[$value] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
        }, $values);
    }
}
