<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Asset;

use Akeneo\Platform\TailoredExport\Domain\Query\FindAssetLabelsInterface;

final class InMemoryFindAssetLabels implements FindAssetLabelsInterface
{
    private array $assetLabels = [];

    public function addAssetLabel(string $assetFamilyCode, string $assetCode, string $locale, string $label)
    {
        $this->assetLabels[$assetFamilyCode][$assetCode][$locale] = $label;
    }

    public function byAssetFamilyCodeAndAssetCodes(string $assetFamilyCode, array $assetCodes, $locale): array
    {
        return array_reduce($assetCodes, function ($carry, $assetCode) use ($assetFamilyCode, $locale) {
            $carry[$assetCode] = $this->assetLabels[$assetFamilyCode][$assetCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
