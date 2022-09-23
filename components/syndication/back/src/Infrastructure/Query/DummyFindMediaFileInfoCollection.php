<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\Query;

use Akeneo\Platform\Syndication\Domain\Query\MediaFileInfo\FindMediaFileInfoCollectionInterface;

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
