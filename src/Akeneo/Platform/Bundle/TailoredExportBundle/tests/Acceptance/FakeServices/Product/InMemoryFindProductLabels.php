<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Product;

use Akeneo\Platform\TailoredExport\Domain\Query\FindProductLabelsInterface;

final class InMemoryFindProductLabels implements FindProductLabelsInterface
{
    private array $productLabels = [];

    public function addProductLabel(
        string $productIdentifier,
        string $channel,
        string $locale,
        string $productTranslation
    ) {
        $this->productLabels[$productIdentifier][$channel][$locale] = $productTranslation;
    }

    public function byIdentifiers(array $productIdentifiers, string $channel, string $locale): array
    {
        return array_reduce($productIdentifiers, function ($carry, $productIdentifier) use ($locale, $channel) {
            $carry[$productIdentifier] = $this->productLabels[$productIdentifier][$channel][$locale] ?? null;

            return $carry;
        }, []);
    }
}
