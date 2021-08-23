<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\MediaFileInfo as AssetManagerMediaFileInfo;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\FindMediaFileInfoCollectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\MediaFileInfo;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindMediaFileInfoCollection implements FindMediaFileInfoCollectionInterface
{
    private GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection;

    public function __construct(GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection)
    {
        $this->getMainMediaFileInfoCollection = $getMainMediaFileInfoCollection;
    }

    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes): array
    {
        $mediaFileInfoCollection = $this->getMainMediaFileInfoCollection->forAssetFamilyAndAssetCodes(
            $assetFamilyIdentifier,
            $assetCodes
        );

        return array_map(
            static fn (AssetManagerMediaFileInfo $assetManagerMediaFileInfo) => new MediaFileInfo(
                $assetManagerMediaFileInfo->getFileKey(),
                $assetManagerMediaFileInfo->getOriginalFilename(),
                $assetManagerMediaFileInfo->getStorage()
            ),
            $mediaFileInfoCollection
        );
    }
}
