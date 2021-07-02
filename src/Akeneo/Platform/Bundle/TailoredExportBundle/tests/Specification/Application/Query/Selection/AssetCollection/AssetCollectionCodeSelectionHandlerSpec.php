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

use Akeneo\Platform\TailoredExport\Application\Query\Selection\AssetCollection\AssetCollectionCodeSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\BooleanValue;
use PhpSpec\ObjectBehavior;

class AssetCollectionCodeSelectionHandlerSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $assetCollectionCodeSelection = new AssetCollectionCodeSelection('/');
        $assetCollectionValue = new AssetCollectionValue(['asset_family_code1', 'asset_family_code2', 'asset_family_code...']);

        $this->applySelection($assetCollectionCodeSelection, $assetCollectionValue)
            ->shouldReturn('asset_family_code1/asset_family_code2/asset_family_code...');
    }

    public function it_does_not_applies_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_asset_collection_code_selection_with_asset_collection_value()
    {
        $assetCollectionCodeSelection = new AssetCollectionCodeSelection('/');
        $assetCollectionValue = new AssetCollectionValue([]);

        $this->supports($assetCollectionCodeSelection, $assetCollectionValue)->shouldReturn(true);
    }

    public function it_does_not_supports_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
