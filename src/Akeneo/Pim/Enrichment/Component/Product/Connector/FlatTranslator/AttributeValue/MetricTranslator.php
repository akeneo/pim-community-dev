<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\SqlGetUnitTranslations;

class MetricTranslator implements FlatAttributeValueTranslatorInterface
{
    private const UNIT_SUFFIX = '-unit';

    /** @var SqlGetUnitTranslations */
    private $getUnitTranslations;

    public function __construct(SqlGetUnitTranslations $getUnitTranslations)
    {
        $this->getUnitTranslations = $getUnitTranslations;
    }

    public function supports(string $attributeType, string $columnName): bool
    {
        $endsWithUnit = 0 === substr_compare($columnName, self::UNIT_SUFFIX, -strlen(self::UNIT_SUFFIX));

        return $attributeType === AttributeTypes::METRIC && $endsWithUnit;
    }

    public function translate(string $attributeCode, array $properties, array $values, string $localeCode): array
    {
        if (!isset($properties['reference_data_name'])) {
            throw new \LogicException(sprintf('Expected properties to have a reference data name to translate metric attribute values'));
        }

        $unitTranslations = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale(
            $properties['reference_data_name'],
            $localeCode
        );

        return array_map(function ($value) use ($unitTranslations) {
            return $unitTranslations[$value] ?? sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
        }, $values);
    }
}
