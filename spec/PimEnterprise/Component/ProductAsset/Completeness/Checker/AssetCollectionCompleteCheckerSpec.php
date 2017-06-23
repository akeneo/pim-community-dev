<?php

namespace spec\PimEnterprise\Component\ProductAsset\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface');
    }

    public function it_suports_asset_collection_attribute(
        ValueInterface $productValue,
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
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        // empty collection
        $value->getData()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_successfully_checks_incomplete_asset_collection(
        ValueInterface $productValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AssetInterface $asset1
    ) {
        $asset1->getVariations()->willReturn([]);
        $productValue->getData()->willReturn([$asset1]);
        $this->isComplete($productValue, $channel, $locale)->shouldReturn(false);
    }
}
