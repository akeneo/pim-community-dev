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

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Platform;

interface GetAssetMainMediaDataInterface
{
    /**
     * @return array    A list of main media values by asset codes filtered by channel and locale. For example:
     *
     * {
     *      "asset_code1" => "data": "http://...",
     *      "asset_code2" => "data": "http://..."
     * }
     */
    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes, ?string $channel, ?string $locale): array;
}
