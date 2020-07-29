<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;

class FamilyFlatTranslator implements PropertyFlatValueTranslatorInterface
{
    /**
     * @var GetFamilyTranslations
     */
    private $getFamilyTranslations;

    public function __construct(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->getFamilyTranslations = $getFamilyTranslations;
    }

    public function supports(string $columnName): bool
    {
        return $columnName === 'family';
    }

    public function translate(array $values, string $locale, string $scope): array
    {
        $familyTranslations = $this->getFamilyTranslations->byFamilyCodesAndLocale($values, $locale);

        $familyLabelized = [];
        foreach ($values as $valueIndex => $value) {
            $familyLabelized[$valueIndex] = $familyTranslations[$value] ?? sprintf('[%s]', $value);
        }

        return $familyLabelized;
    }
}
