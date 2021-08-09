<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindAssetLabelsInterface
{
    /**
     * @return array<string, string>
     */
    public function byAssetFamilyCodeAndAssetCodes(
        string $assetFamilyCode,
        array $assetCodes,
        string $locale
    ): array;
}
