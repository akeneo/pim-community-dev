<?php

namespace spec\PimEnterprise\Component\ProductAsset\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Factory\VariationFactory;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;

class ReferenceFactorySpec extends ObjectBehavior
{
    const REFERENCE_CLASS = 'PimEnterprise\Component\ProductAsset\Model\Reference';

    function let(ChannelRepositoryInterface $channelRepository, VariationFactory $variationFactory)
    {
        $this->beConstructedWith($channelRepository, $variationFactory, self::REFERENCE_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Factory\ReferenceFactory');
    }

    function it_creates_a_not_localized_reference(
        $channelRepository,
        $variationFactory,
        ChannelInterface $print,
        ChannelInterface $mobile,
        VariationInterface $variationPrint,
        VariationInterface $variationMobile
    ) {
        $channelRepository->getFullChannels()->willReturn([$print, $mobile]);

        $variationFactory->create($print)->willReturn($variationPrint);
        $variationFactory->create($mobile)->willReturn($variationMobile);

        $this->create()->shouldReturnAnInstanceOf(self::REFERENCE_CLASS);
    }

    function it_creates_a_localized_reference(
        $variationFactory,
        LocaleInterface $fr_FR,
        ChannelInterface $print,
        ChannelInterface $mobile,
        VariationInterface $variationPrint,
        VariationInterface $variationMobile
    ) {
        $fr_FR->getChannels()->willReturn([$print, $mobile]);

        $variationFactory->create($print)->willReturn($variationPrint);
        $variationFactory->create($mobile)->willReturn($variationMobile);

        $this->create($fr_FR)->shouldReturnAnInstanceOf(self::REFERENCE_CLASS);
    }
}
