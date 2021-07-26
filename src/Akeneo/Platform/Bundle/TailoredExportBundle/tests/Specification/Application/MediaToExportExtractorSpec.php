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

namespace Specification\Akeneo\Platform\TailoredExport\Application;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\GetMainMediaFileInfoCollectionInterface;
use Akeneo\Platform\TailoredExport\Application\MediaToExportExtractor;
use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Domain\MediaToExport;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class MediaToExportExtractorSpec extends ObjectBehavior
{
    public function let(GetMainMediaFileInfoCollectionInterface $getMainMediaFileInfoCollection)
    {
        $this->beConstructedWith($getMainMediaFileInfoCollection);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(MediaToExportExtractor::class);
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

        $expectedMediaToExport = [];
        $expectedMediaToExport['a_filekey'] = new MediaToExport(
            'a_filekey',
            'catalog',
            'files/an_id/an_attribute_code/an_original_filename'
        );

        $mediaToExport = $this->extract($columnCollection, $valueCollection);
        $mediaToExport->shouldHaveCount(1);
        $mediaToExport->shouldBeLike($expectedMediaToExport);
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

        $mainMediaFileInfo = new FileInfo();
        $mainMediaFileInfo->setKey('a_filekey');
        $mainMediaFileInfo->setOriginalFilename('an_original_filename');

        $getMainMediaFileInfoCollection->forAssetFamilyAndAssetCodes(
            'a_family_code',
            ['asset_code_1', 'asset_code_2', 'asset_code_3'],
            null,
            null
        )->willReturn([
            $mainMediaFileInfo
        ]);

        $expectedMediaToExport = [];
        $expectedMediaToExport['a_filekey'] = new MediaToExport(
            'a_filekey',
            'assetStorage',
            'files/an_id/an_attribute_code/an_original_filename'
        );

        $mediaToExport = $this->extract($columnCollection, $valueCollection);
        $mediaToExport->shouldHaveCount(1);
        $mediaToExport->shouldBeLike($expectedMediaToExport);
    }
}
