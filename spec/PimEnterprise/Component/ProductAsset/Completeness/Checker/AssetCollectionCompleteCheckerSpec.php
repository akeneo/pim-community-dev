<?php

namespace spec\PimEnterprise\Component\ProductAsset\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_asset_collection_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getType()->willReturn('pim_assets_collection');
        $this->supportsValue($productValue, $channel, $locale)->shouldReturn(true);

        $attribute->getType()->willReturn('other');
        $this->supportsValue($productValue, $channel, $locale)->shouldReturn(false);
    }

    public function it_succesfully_checks_empty_asset_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getAssets()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        // empty collection
        $value->getAssets()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_successfully_checks_incomplete_asset_collection(
        ProductValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AssetInterface $asset1
    ) {
        $asset1->getVariations()->willReturn([]);
        $productValue->getAssets()->willReturn([$asset1]);
        $this->isComplete($productValue, $channel, $locale)->shouldReturn(false);
    }
}
