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

use Akeneo\Platform\TailoredExport\Application\Common\Selection\AssetCollection\AssetCollectionMediaLinkSelection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\AssetCollectionValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Query\FindMediaLinksInterface;
use PhpSpec\ObjectBehavior;

class AssetCollectionMediaLinkSelectionApplierSpec extends ObjectBehavior
{
    public function let(FindMediaLinksInterface $findAssetMediaLinks)
    {
        $this->beConstructedWith($findAssetMediaLinks);
    }

    public function it_applies_the_selection(FindMediaLinksInterface $findAssetMediaLinks)
    {
        $selection = new AssetCollectionMediaLinkSelection(
            ';',
            'ecommerce',
            'fr_FR',
            'an_asset_family_code',
            'foo_attribute_code'
        );
        $value = new AssetCollectionValue(
            ['asset_code1', 'asset_code2'],
            'the_identifier',
            null,
            null
        );

        $findAssetMediaLinks->forScopedAndLocalizedAssetFamilyAndAssetCodes(
            'an_asset_family_code',
            ['asset_code1', 'asset_code2'],
            'ecommerce',
            'fr_FR',
        )->willReturn(['http://test.fr', 'http://test.com']);

        $this->applySelection($selection, $value)
            ->shouldReturn('http://test.fr;http://test.com');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply Asset Collection selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_asset_collection_media_link_selection_with_asset_collection_value()
    {
        $selection = new AssetCollectionMediaLinkSelection(
            '/',
            'ecommerce',
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
