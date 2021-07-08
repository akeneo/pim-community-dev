<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;

final class InMemoryGetProductModelLabels implements GetProductModelLabelsInterface
{
    private array $productModelLabels = [];

    public function addProductModelLabel(
        string $productModelCode,
        string $channel,
        string $locale,
        string $productModelTranslation
    ) {
        $this->productModelLabels[$productModelCode][$channel][$locale] = $productModelTranslation;
    }

    public function byCodesAndLocaleAndScope(array $codes, string $locale, string $scope): array
    {
        return array_reduce($codes, function ($carry, $productModelCode) use ($locale, $scope) {
            $carry[$productModelCode] = $this->productModelLabels[$productModelCode][$scope][$locale] ?? null;

            return $carry;
        }, []);
    }
}
