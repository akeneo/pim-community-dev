<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;

class ParentTranslator implements FlatPropertyValueTranslatorInterface
{
    /** @var GetProductModelLabelsInterface */
    private $getProductModelLabels;

    public function __construct(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function supports(string $columnName): bool
    {
        return 'parent' === $columnName;
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

            $result[$valueIndex] = $productModelLabels[$parentCode] ??
                sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $parentCode);
        }

        return $result;
    }
}
