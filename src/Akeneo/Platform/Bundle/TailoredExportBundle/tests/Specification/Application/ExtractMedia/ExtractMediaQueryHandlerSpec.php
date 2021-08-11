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

namespace Specification\Akeneo\Platform\TailoredExport\Application\ExtractMedia;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\MediaFileInfo;
use Akeneo\Platform\TailoredExport\Application\Common\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractedMedia;
use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractMediaQueryHandler;
use PhpSpec\ObjectBehavior;

class ExtractMediaQueryHandlerSpec extends ObjectBehavior
{
    public function let(GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection)
    {
        $this->beConstructedWith($getMainMediaFileInfoCollection);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ExtractMediaQueryHandler::class);
    }

    public function it_extracts_file_media_to_exports(): void
    {
        $operationCollection = OperationCollection::create([]);
        $source = new AttributeSource(
            'pim_catalog_file',
            'a_code',
            null,
            null,
            $operationCollection,
            new FilePathSelection('an_attribute_code')
        );
        $column = new Column('target1', SourceCollection::create([$source]));

        $columnCollection = ColumnCollection::create(
            [$column]
        );

        $valueCollection = new ValueCollection();
        $fileValue = new FileValue(
            'an_id',
            'catalog',
            'a_filekey',
            'an_original_filename',
            null,
            null
        );
        $valueCollection->add(
            $fileValue,
            'a_code',
            null,
            null
        );

        $expectedExtractedMedia = [
            new ExtractedMedia(
                'a_filekey',
                'catalog',
                'files/an_id/an_attribute_code/an_original_filename'
            )
        ];

        $mediaToExport = $this->handle($columnCollection, $valueCollection);
        $mediaToExport->shouldHaveCount(1);
        $mediaToExport->shouldBeLike($expectedExtractedMedia);
    }

    public function it_extracts_asset_to_exports(GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection): void
    {
        $operationCollection = OperationCollection::create([]);
        $source = new AttributeSource(
            'pim_catalog_asset_collection',
            'a_code',
            null,
            null,
            $operationCollection,
            new AssetCollectionCodeSelection('/', 'a_family_code', 'an_attribute_code')
        );
        $column = new Column('target1', SourceCollection::create([$source]));

        $columnCollection = ColumnCollection::create(
            [$column]
        );

        $valueCollection = new ValueCollection();
        $assetCollectionValue = new AssetCollectionValue(
            ['asset_code_1', 'asset_code_2', 'asset_code_3'],
            'an_id',
            null,
            null
        );
        $valueCollection->add(
            $assetCollectionValue,
            'a_code',
            null,
            null
        );

        $getMainMediaFileInfoCollection->forAssetFamilyAndAssetCodes(
            'a_family_code',
            ['asset_code_1', 'asset_code_2', 'asset_code_3']
        )->willReturn([new MediaFileInfo('a_filekey', 'an_original_filename', 'assetStorage')]);

        $expectedExtractedMedia = [
            new ExtractedMedia(
                'a_filekey',
                'assetStorage',
                'files/an_id/an_attribute_code/an_original_filename'
            )
        ];

        $mediaToExport = $this->handle($columnCollection, $valueCollection);
        $mediaToExport->shouldBeLike($expectedExtractedMedia);
    }
}
