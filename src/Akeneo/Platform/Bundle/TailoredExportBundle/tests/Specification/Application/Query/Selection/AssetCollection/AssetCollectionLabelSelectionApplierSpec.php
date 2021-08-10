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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindAssetLabelsInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionLabelSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindAssetLabelsInterface $findAssetLabels)
    {
        $this->beConstructedWith($findAssetLabels);
    }

    public function it_applies_the_selection(FindAssetLabelsInterface $findAssetLabels)
    {
        $selection = new AssetCollectionLabelSelection(
            '/',
            'fr_FR',
            'an_asset_family_code',
            'foo_attribute_code'
        );
        $value = new AssetCollectionValue(
            ['asset_code1', 'asset_code2', 'asset_code...'],
            'the_identifier',
            null,
            null
        );

        $findAssetLabels->byAssetFamilyCodeAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2', 'asset_code...'],
            'fr_FR'
        )->willReturn([
            'asset_code2' => 'label2',
            'asset_code...' => 'label...',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('[asset_code1]/label2/label...');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_asset_collection_label_selection_with_asset_collection_value()
    {
        $selection = new AssetCollectionLabelSelection(
            '/',
            'fr_FR',
            'an_asset_family_code',
            'foo_attribute_code'
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
}
