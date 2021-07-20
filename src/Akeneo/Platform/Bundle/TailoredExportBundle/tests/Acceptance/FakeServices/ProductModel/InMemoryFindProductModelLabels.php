<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\ProductModel;

use Akeneo\Platform\TailoredExport\Domain\Query\FindProductModelLabelsInterface;

final class InMemoryFindProductModelLabels implements FindProductModelLabelsInterface
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

    public function byCodes(array $productModelCodes, string $channel, string $locale): array
    {
        return array_reduce($productModelCodes, function ($carry, $productModelCode) use ($locale, $channel) {
            $carry[$productModelCode] = $this->productModelLabels[$productModelCode][$channel][$locale] ?? null;

            return $carry;
        }, []);
    }
}
