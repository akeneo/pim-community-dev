<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyTranslator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;

class CategoryFlatTranslator implements PropertyFlatTranslator
{
    /**
     * @var GetCategoryTranslations
     */
    private $getCategoryTranslations;

    public function __construct(GetCategoryTranslations $getCategoryTranslations)
    {
        $this->getCategoryTranslations = $getCategoryTranslations;
    }

    public function support(string $columnName): bool
    {
        return $columnName === 'categories';
    }

    public function translateValues(array $values, string $locale): array
    {
        $categoryCodesExtracted = $this->extractCategoryCodes($values);
        $categoryTranslations = $this->getCategoryTranslations->byCategoryCodesAndLocale($categoryCodesExtracted, $locale);

        foreach ($values as $valueIndex => $value) {
            if (empty($value)) {
                $result[$valueIndex] = $value;
                continue;
            }

            $categoryCodes = explode(',', $value);
            $categoriesLabelized = [];

            foreach ($categoryCodes as $categoryCode) {
                $categoriesLabelized[] = $categoryTranslations[$categoryCode] ?? sprintf('[%s]', $categoryCode);
            }

            $result[$valueIndex] = implode(',', $categoriesLabelized);
        }

        return $result;
    }

    private function extractCategoryCodes(array $values): array
    {
        $categoryCodes = [];
        foreach ($values as $value) {
            $categoryCodes = array_merge($categoryCodes, explode(',', $value));
        }

        return array_unique($categoryCodes);
    }
}
