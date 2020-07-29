<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatPropertyValueTranslator;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\GetProductModelLabel;

class ParentFlatTranslator implements PropertyFlatTranslatorInterface
{
    private $getProductModelLabels;

    public function __construct(GetProductModelLabel $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function support(string $columnName): bool
    {
        return $columnName === 'parent';
    }

    public function translateValues(array $parentCodes, string $locale): array
    {
        $result = [];
        $productModelLabels = $this->getProductModelLabels->byCodesAndLocaleAndScope($parentCodes, $locale);
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
