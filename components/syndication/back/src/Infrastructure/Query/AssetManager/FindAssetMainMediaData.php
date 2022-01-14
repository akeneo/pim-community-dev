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

namespace Akeneo\Platform\Syndication\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Platform\GetAssetMainMediaDataInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface;

class FindAssetMainMediaData implements FindAssetMainMediaDataInterface
{
    private GetAssetMainMediaDataInterface $getAssetMainMediaData;

    public function __construct(GetAssetMainMediaDataInterface $getAssetMainMediaData)
    {
        $this->getAssetMainMediaData = $getAssetMainMediaData;
    }

    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes, ?string $channel, ?string $locale): array
    {
        return $this->getAssetMainMediaData->forAssetFamilyAndAssetCodes($assetFamilyIdentifier, $assetCodes, $channel, $locale);
    }
}
