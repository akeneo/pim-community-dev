<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;

class FamilyVariantTranslator implements FlatPropertyValueTranslatorInterface
{
    /** @var GetFamilyVariantTranslations */
    private $getFamilyVariantTranslations;

    public function __construct(GetFamilyVariantTranslations $getFamilyVariantTranslations)
    {
        $this->getFamilyVariantTranslations = $getFamilyVariantTranslations;
    }

    public function supports(string $columnName): bool
    {
        return 'family_variant' === $columnName;
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $familyVariantTranslations = $this->getFamilyVariantTranslations->byFamilyVariantCodesAndLocale($values, $locale);

        $familyVariantLabelized = [];
        foreach ($values as $valueIndex => $value) {
            $familyVariantLabelized[$valueIndex] = $familyVariantTranslations[$value] ??
                sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $value);
        }

        return $familyVariantLabelized;
    }
}
