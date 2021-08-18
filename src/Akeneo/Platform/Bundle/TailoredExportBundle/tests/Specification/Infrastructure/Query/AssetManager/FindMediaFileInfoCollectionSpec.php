<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\MediaFileInfo as AssetManagerMediaFileInfo;
use Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo\MediaFileInfo;
use Akeneo\Platform\TailoredExport\Infrastructure\Query\AssetManager\FindMediaFileInfoCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindMediaFileInfoCollectionSpec extends ObjectBehavior
{
    public function let(
        GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection
    ): void {
        $this->beConstructedWith($getMainMediaFileInfoCollection);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(FindMediaFileInfoCollection::class);
    }

    public function it_gets_media_file_info_collections(
        GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection
    ): void {
        $assetFamilyCode = 'images';
        $assetCodes = ['atmosphere1', 'atmosphere2'];

        $getMainMediaFileInfoCollection->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)
            ->willReturn(
                [
                    new AssetManagerMediaFileInfo('fileKey1', 'originalFilename1', 'storage1'),
                    new AssetManagerMediaFileInfo('fileKey2', 'originalFilename2', 'storage2'),
                ]
            );

        $this->forAssetFamilyAndAssetCodes($assetFamilyCode, $assetCodes)->shouldBeLike(
            [
                new MediaFileInfo('fileKey1', 'originalFilename1', 'storage1'),
                new MediaFileInfo('fileKey2', 'originalFilename2', 'storage2'),
            ]
        );
    }
}
