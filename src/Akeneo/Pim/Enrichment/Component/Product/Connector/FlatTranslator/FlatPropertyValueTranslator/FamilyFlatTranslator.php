<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;

class FamilyFlatTranslator implements PropertyFlatTranslatorInterface
{
    /**
     * @var GetFamilyTranslations
     */
    private $getFamilyTranslations;

    public function __construct(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->getFamilyTranslations = $getFamilyTranslations;
    }

    public function support(string $columnName): bool
    {
        return $columnName === 'family';
    }

    public function translateValues(array $values, string $locale): array
    {
        $familyTranslations = $this->getFamilyTranslations->byFamilyCodesAndLocale($values, $locale);

        $familyLabelized = [];
        foreach ($values as $valueIndex => $value) {
            $familyLabelized[$valueIndex] = $familyTranslations[$value] ?? sprintf('[%s]', $value);
        }

        return $familyLabelized;
    }
}
