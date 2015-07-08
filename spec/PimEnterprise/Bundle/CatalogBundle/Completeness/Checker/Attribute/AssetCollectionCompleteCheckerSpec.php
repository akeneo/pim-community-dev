<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Completeness\Checker\Attribute;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use PimEnterprise\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

class AssetCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_suports_asset_collection_attribute(
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_assets_collection');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    public function it_succesfully_checks_empty_asset_collection(
        ProductValueInterface $value,
        ChannelInterface $channel
    ) {
        $value->getAssets()->willReturn(null);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        // empty collection
        $value->getAssets()->willReturn(new ArrayCollection());
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }

    public function it_succesfully_checks_incomplete_asset_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        AssetInterface $asset1
    ) {
        $asset1->getVariations()->willReturn([]);
        $value->getAssets()->willReturn(new ArrayCollection([$asset1]));
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }
}
