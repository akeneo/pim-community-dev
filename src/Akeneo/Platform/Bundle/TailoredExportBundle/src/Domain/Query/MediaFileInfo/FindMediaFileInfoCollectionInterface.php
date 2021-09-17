<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindMediaFileInfoCollectionInterface
{
    /**
     * @return MediaFileInfo[]
     */
    public function forAssetFamilyAndAssetCodes(
        string $assetFamilyIdentifier,
        array $assetCodes
    ): array;

    /**
     * @return MediaFileInfo[]
     */
    public function forScopedAndLocalizedAssetFamilyAndAssetCodes(
        string $assetFamilyIdentifier,
        array $assetCodes,
        ?string $channel,
        ?string $locale
    ): array;
}
