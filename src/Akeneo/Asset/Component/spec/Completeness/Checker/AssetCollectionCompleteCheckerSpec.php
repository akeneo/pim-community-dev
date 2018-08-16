<?php

namespace spec\Akeneo\Asset\Component\Completeness\Checker;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Asset\Component\Model\AssetInterface;

class AssetCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement(ValueCompleteCheckerInterface::class);
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
