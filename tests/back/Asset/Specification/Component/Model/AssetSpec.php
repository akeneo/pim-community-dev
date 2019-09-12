<?php

namespace Specification\Akeneo\Asset\Component\Model;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use PhpSpec\ObjectBehavior;

class AssetSpec extends ObjectBehavior
{
    function it_is_a_versionnable_asset()
    {
        $this->shouldImplement(AssetInterface::class);
        $this->shouldImplement(VersionableInterface::class);
    }

    function it_gets_no_reference_when_empty()
    {
        $this->getReference()->shouldReturn(null);
    }

    function it_gets_reference_when_localizable(
        ReferenceInterface $reference1,
        ReferenceInterface $reference2,
        LocaleInterface $locale1,
        LocaleInterface $locale2,
        LocaleInterface $locale3
    ) {
        $reference1->getLocale()->willReturn($locale1);
        $reference2->getLocale()->willReturn($locale2);
        $this->addReference($reference1);
        $this->addReference($reference2);
        $this->getReference($locale1)->shouldReturn($reference1);
        $this->getReference($locale2)->shouldNotReturn($reference1);
        $this->getReference($locale2)->shouldReturn($reference2);
        $this->getReference($locale3)->shouldReturn(null);
    }

    function it_throws_an_exception_when_getting_reference_without_specifying_locale(
        ReferenceInterface $reference1,
        ReferenceInterface $reference2,
        LocaleInterface $locale1,
        LocaleInterface $locale2
    ) {
        $reference1->getLocale()->willReturn($locale1);
        $reference2->getLocale()->willReturn($locale2);
        $this->addReference($reference1);
        $this->addReference($reference2);

        $this->shouldThrow(\LogicException::class)->during('getReference');
    }

    function it_gets_file_for_context(
        ReferenceInterface $reference,
        VariationInterface $variationEcommerce,
        VariationInterface $variationMobile,
        FileInfo $variationEcommerceFileInfo,
        FileInfo $variationMobileFileInfo,
        ChannelInterface $channelEcommerce,
        ChannelInterface $channelMobile
    ) {
        $this->getFileForContext($channelEcommerce)->shouldReturn(null);

        $reference->getLocale()->willReturn(null);

        $reference->getVariation($channelEcommerce)->willReturn($variationEcommerce);
        $variationEcommerce->getFileInfo()->willReturn($variationEcommerceFileInfo);
        $reference->getVariation($channelMobile)->willReturn($variationMobile);
        $variationMobile->getFileInfo()->willReturn($variationMobileFileInfo);

        $this->addReference($reference);

        $this->getFileForContext($channelEcommerce)->shouldReturn($variationEcommerceFileInfo);
        $this->getFileForContext($channelMobile)->shouldReturn($variationMobileFileInfo);
    }

    function it_returns_null_if_no_variation_exists_for_a_given_context(
        ReferenceInterface $reference
    ) {
        $ecommerce = new Channel();
        $reference->getVariation($ecommerce)->willReturn(null);

        $this->getFileForContext($ecommerce)->shouldReturn(null);
    }

    function it_returns_null_if_variation_has_no_file_info_for_a_given_context(
        ReferenceInterface $reference,
        VariationInterface $variation
    ) {
        $ecommerce = new Channel();
        $variation->getFileInfo()->willReturn(null);
        $reference->getVariation($ecommerce)->willReturn($variation);

        $this->getFileForContext($ecommerce)->shouldReturn(null);
    }
}
