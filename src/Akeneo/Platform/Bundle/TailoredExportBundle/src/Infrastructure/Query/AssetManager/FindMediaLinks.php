<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetAssetMainMediaValuesInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindMediaLinksInterface;

class FindMediaLinks implements FindMediaLinksInterface
{
    private GetAssetMainMediaValuesInterface $getAssetMainMediaValues;

    public function __construct(GetAssetMainMediaValuesInterface $getAssetMainMediaValues)
    {
        $this->getAssetMainMediaValues = $getAssetMainMediaValues;
    }

    public function forScopedAndLocalizedAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes, ?string $channel, ?string $locale): array
    {
        $mainMediaLinkValues = $this->getAssetMainMediaValues->forAssetFamilyAndAssetCodes($assetFamilyIdentifier, $assetCodes);
        $scopedAndLocalizedMainMediaLinkValues = [];
        foreach ($mainMediaLinkValues as $mainMediaLinkValue) {
            $scopedAndLocalizedMainMediaLinkValues = [
                ...$scopedAndLocalizedMainMediaLinkValues,
                ...array_filter($mainMediaLinkValue, fn ($value) => $channel === $value['channel'] && $locale === $value['locale'])
            ];
        }

        return array_map(static fn ($value) => $value['data'], $scopedAndLocalizedMainMediaLinkValues);
    }
}
