<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\MediaFileInfo as AssetManagerMediaFileInfo;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\FindMediaFileInfoCollectionInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\MediaFileInfo;

/**
 * @author    Benoit Jacquemont <benoitil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyFindMediaFileInfoCollection implements FindMediaFileInfoCollectionInterface
{
    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes): array
    {
        return [];
    }
}
