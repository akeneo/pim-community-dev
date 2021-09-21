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

namespace Specification\Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\AssetCollection;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaFileSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAssetMainMediaDataInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionMediaFileSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindAssetMainMediaDataInterface $findAssetMainMediatData)
    {
        $this->beConstructedWith($findAssetMainMediatData);
    }

    public function it_applies_the_selection_with_property_equals_to_file_key(FindAssetMainMediaDataInterface $findAssetMainMediatData)
    {
        $selection = $this->createAssetCollectionMediaFileSelection('file_key');
        $value = $this->createAssetCollectionValue();

        $findAssetMainMediatData->forAssetFamilyAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2'],
            'ecommerce',
            'fr_FR',
        )->willReturn([
            ['fileKey' => 'filekey1',  'filePath' => 'filepath1', 'originalFilename' => 'filename1'],
            ['fileKey' => 'filekey2',  'filePath' => 'filepath2', 'originalFilename' => 'filename2'],
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('filekey1;filekey2');
    }

    public function it_applies_the_selection_with_property_equals_to_file_path(FindAssetMainMediaDataInterface $findAssetMainMediatData)
    {
        $selection = $this->createAssetCollectionMediaFileSelection('file_path');
        $value = $this->createAssetCollectionValue();

        $findAssetMainMediatData->forAssetFamilyAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2'],
            'ecommerce',
            'fr_FR',
        )->willReturn([
            ['fileKey' => 'filekey1',  'filePath' => 'filepath1', 'originalFilename' => 'filename1'],
            ['fileKey' => 'filekey2',  'filePath' => 'filepath2', 'originalFilename' => 'filename2'],
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('filepath1;filepath2');
    }

    public function it_applies_the_selection_with_property_equals_to_original_file_name(FindAssetMainMediaDataInterface $findAssetMainMediatData)
    {
        $selection = $this->createAssetCollectionMediaFileSelection('original_file_name');
        $value = $this->createAssetCollectionValue();

        $findAssetMainMediatData->forAssetFamilyAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2'],
            'ecommerce',
            'fr_FR',
        )->willReturn([
            ['fileKey' => 'filekey1',  'filePath' => 'filepath1', 'originalFilename' => 'filename1'],
            ['fileKey' => 'filekey2',  'filePath' => 'filepath2', 'originalFilename' => 'filename2'],
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('filename1;filename2');
    }

    public function it_throws_invalid_argument_expection_when_selection_have_an_invalid_property(FindAssetMainMediaDataInterface $findAssetMainMediatData)
    {
        $invalidSelection = $this->createAssetCollectionMediaFileSelection('invalid_type');
        $value = $this->createAssetCollectionValue();

        $findAssetMainMediatData->forAssetFamilyAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2'],
            'ecommerce',
            'fr_FR',
        )->willReturn([
            ['fileKey' => 'filekey1',  'filePath' => 'filepath1', 'originalFilename' => 'filename1'],
            ['fileKey' => 'filekey2',  'filePath' => 'filepath2', 'originalFilename' => 'filename2'],
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity'))
            ->during('applySelection', [$invalidSelection, $value]);
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_asset_collection_media_file_selection_with_asset_collection_value()
    {
        $selection = new AssetCollectionMediaFileSelection(
            '/',
            'ecommerce',
            'fr_FR',
            'an_asset_family_code',
            'foo_attribute_code',
            'file_key'
        );
        $value = new AssetCollectionValue([], 'an_id', null, null);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    private function createAssetCollectionValue(): AssetCollectionValue
    {
        return new AssetCollectionValue(
            ['asset_code1', 'asset_code2'],
            'the_identifier',
            null,
            null
        );
    }

    private function createAssetCollectionMediaFileSelection(string $property): AssetCollectionMediaFileSelection
    {
        return new AssetCollectionMediaFileSelection(
            ';',
            'ecommerce',
            'fr_FR',
            'an_asset_family_code',
            'foo_attribute_code',
            $property
        );
    }
}
