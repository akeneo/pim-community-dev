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

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslation;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionLabelSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class AssetCollectionLabelSelectionHandlerSpec extends ObjectBehavior
{
    public function let(FindAssetLabelTranslation $findAssetLabelTranslations)
    {
        $this->beConstructedWith($findAssetLabelTranslations);
    }

    public function it_applies_the_selection(FindAssetLabelTranslation $findAssetLabelTranslations)
    {
        $selection = new AssetCollectionLabelSelection(
            '/',
            'fr_FR',
            'an_asset_family_code'
        );
        $value = new AssetCollectionValue(['asset_code1', 'asset_code2', 'asset_code...']);

        $findAssetLabelTranslations->byFamilyCodeAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2', 'asset_code...'],
            'fr_FR'
        )->willReturn([
            'asset_code1' => 'label1',
            'asset_code2' => 'label2',
            'asset_code...' => 'label...',
        ]);

        $this->applySelection($selection, $value)
            ->shouldReturn('label1/label2/label...');
    }

    public function it_does_not_applies_selection_on_not_supported_selections_and_values()
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
            'an_asset_family_code'
        );
        $value = new AssetCollectionValue([]);

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_supports_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
