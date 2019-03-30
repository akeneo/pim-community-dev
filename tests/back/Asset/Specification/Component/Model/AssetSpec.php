<?php

namespace Specification\Akeneo\Asset\Component\Model;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
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

    function it_throws_when_getting_reference_without_specifying_locale(
        ReferenceInterface $reference1,
        ReferenceInterface $reference2,
        LocaleInterface $locale1,
        LocaleInterface $locale2
    ) {
        $reference1->getLocale()->willReturn($locale1);
        $reference2->getLocale()->willReturn($locale2);
        $this->addReference($reference1);
        $this->addReference($reference2);

        $this->shouldThrow(\LogicException::class)->during('getReference');;
    }

    function it_gets_file_for_context(
        ReferenceInterface $reference1,
        ReferenceInterface $reference2,
        VariationInterface $variation1,
        VariationInterface $variation2,
        FileInfo $variationFileInfo,
        FileInfo $reference1FileInfo,
        FileInfo $reference2FileInfo,
        LocaleInterface $localeEnUs,
        LocaleInterface $localeFrFr,
        ChannelInterface $channelEcommerce,
        ChannelInterface $channelMobile
    ) {
        $this->getFileForContext($channelEcommerce)->shouldReturn(null);
        $this->getFileForContext($channelEcommerce, $localeEnUs)->shouldReturn(null);

        $reference1->getLocale()->willReturn($localeEnUs);
        $reference2->getLocale()->willReturn($localeFrFr);

        $reference1->getFileInfo()->willReturn($reference1FileInfo);
        $reference2->getFileInfo()->willReturn($reference2FileInfo);

        $reference1->getVariation($channelEcommerce)->willReturn($variation1);
        $variation1->getFileInfo()->willReturn($variationFileInfo);
        $reference1->getVariation($channelMobile)->willReturn($variation2);
        $variation2->getFileInfo()->willReturn(null);

        $reference2->getVariation($channelEcommerce)->willReturn(null);

        $this->addReference($reference1);
        $this->addReference($reference2);

        $this->shouldThrow(\LogicException::class)->during('getReference');;

        $this->getFileForContext($channelEcommerce, $localeEnUs)->shouldReturn($variationFileInfo);
        $this->getFileForContext($channelMobile, $localeEnUs)->shouldReturn($reference1FileInfo);
        $this->getFileForContext($channelEcommerce, $localeFrFr)->shouldReturn($reference2FileInfo);
    }
}
