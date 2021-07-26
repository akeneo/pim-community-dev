<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
interface GetMainMediaFileInfoCollectionInterface
{
    /**
     * @return MediaFileInfo[]
     */
    public function forAssetFamilyAndAssetCodes(
        string $assetFamilyIdentifier,
        array $assetCodes,
        ?string $channelReference,
        ?string $localeReference
    ): array;
}
