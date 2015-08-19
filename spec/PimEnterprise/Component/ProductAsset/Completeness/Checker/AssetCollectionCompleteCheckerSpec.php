<?php

namespace spec\PimEnterprise\Component\ProductAsset\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_asset_collection_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_assets_collection');
        $this->supportsValue($productValue)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsValue($productValue)->shouldReturn(false);
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

    public function it_succesfully_checks_incomplete_asset_collection(
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
