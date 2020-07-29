<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;

class ParentFlatTranslator implements PropertyFlatValueTranslatorInterface
{
    /**
     * @var GetProductModelLabelsInterface
     */
    private $getProductModelLabels;

    public function __construct(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function supports(string $columnName): bool
    {
        return $columnName === 'parent';
    }

    public function translate(array $parentCodes, string $locale, string $scope): array
    {
        $productModelLabels = $this->getProductModelLabels->byCodesAndLocaleAndScope($parentCodes, $locale, $scope);

        $result = [];
        foreach ($parentCodes as $valueIndex => $parentCode) {
            if (empty($parentCode)) {
                $result[$valueIndex] = $parentCode;
                continue;
            }

            $result[$valueIndex] = $productModelLabels[$parentCode] ?? sprintf('[%s]', $parentCode);
        }

        return  $result;
    }
}
