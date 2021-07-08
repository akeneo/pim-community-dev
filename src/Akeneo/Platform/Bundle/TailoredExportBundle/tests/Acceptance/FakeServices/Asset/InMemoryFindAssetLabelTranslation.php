<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\FakeServices\Asset;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslationInterface;

final class InMemoryFindAssetLabelTranslation implements FindAssetLabelTranslationInterface
{
    private array $assetLabels = [];

    public function addAssetLabel(string $assetFamilyCode, string $assetCode, string $locale, string $label)
    {
        $this->assetLabels[$assetFamilyCode][$assetCode][$locale] = $label;
    }

    public function byFamilyCodeAndAssetCodes(string $familyCode, array $assetCodes, $locale): array
    {
        return array_reduce($assetCodes, function ($carry, $assetCode) use ($familyCode, $locale) {
            $carry[$assetCode] = $this->assetLabels[$familyCode][$assetCode][$locale] ?? null;

            return $carry;
        }, []);
    }
}
