<?php

namespace spec\PimEnterprise\Component\ProductAsset\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use Prophecy\Argument;

class VariationBuilderSpec extends ObjectBehavior
{
    function let(
        ChannelRepositoryInterface $repository,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        ChannelInterface $mobile,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR,
        LocaleInterface $de_DE
    ) {
        $en_US->isActivated()->willReturn(true);
        $fr_FR->isActivated()->willReturn(true);
        $de_DE->isActivated()->willReturn(true);

        $channels = [$ecommerce, $print, $mobile];
        $repository->getFullChannels()->willReturn($channels);

        $this->beConstructedWith($repository);
    }

    function it_builds_missing_variations_on_a_non_localized_reference(
        $ecommerce,
        $print,
        $mobile,
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn(null);

        $reference->hasVariation($ecommerce)->willReturn(false);
        $reference->hasVariation($print)->willReturn(true);
        $reference->hasVariation($mobile)->willReturn(false);

        $reference->addVariation(Argument::any())->shouldBeCalledTimes(2);

        $missings = $this->buildMissing($reference);
        $missings->shouldHaveCount(2);
        $missings->shouldBeArrayOfVariations();
    }

    function it_builds_missing_variations_on_a_localized_reference(
        $en_US,
        $ecommerce,
        $print,
        $mobile,
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn($en_US);
        $ecommerce->hasLocale($en_US)->willReturn(true);
        $print->hasLocale($en_US)->willReturn(false);
        $mobile->hasLocale($en_US)->willReturn(true);

        $reference->hasVariation($ecommerce)->willReturn(false);
        $reference->hasVariation($mobile)->willReturn(false);

        $reference->addVariation(Argument::any())->shouldBeCalledTimes(2);

        $missings = $this->buildMissing($reference);
        $missings->shouldHaveCount(2);
        $missings->shouldBeArrayOfVariations();
    }

    function it_builds_a_variation($ecommerce, ReferenceInterface $reference)
    {
        $variation = $this->buildOne($reference, $ecommerce);

        $variation->getReference()->shouldBe($reference);
        $variation->getChannel()->shouldBe($ecommerce);
    }

    function it_does_not_build_a_variation_if_the_channel_has_not_the_locale_of_the_reference(
        ReferenceInterface $reference,
        ChannelInterface $tablet,
        LocaleInterface $it_IT
    ) {
        $it_IT->getCode()->willReturn('it_IT');
        $it_IT->isActivated()->willReturn(true);
        $tablet->hasLocale($it_IT)->willReturn(false);
        $tablet->getCode()->willReturn('tablet');
        $reference->getLocale()->willReturn($it_IT);

        $this->shouldThrow(
            new \LogicException(
                'Impossible to build a variation on channel "tablet" for the reference with locale "it_IT".'
            )
        )->during('buildOne', [$reference, $tablet]);
    }

    function it_does_not_build_a_variation_if_the_channel_of_the_reference_is_not_activated(
        ReferenceInterface $reference,
        ChannelInterface $tablet,
        LocaleInterface $it_IT
    ) {
        $it_IT->getCode()->willReturn('it_IT');
        $it_IT->isActivated()->willReturn(false);
        $tablet->hasLocale($it_IT)->willReturn(true);
        $tablet->getCode()->willReturn('tablet');
        $reference->getLocale()->willReturn($it_IT);

        $this->shouldThrow(
            new \LogicException(
                'Impossible to build a variation on channel "tablet" for the reference with locale "it_IT".'
            )
        )->during('buildOne', [$reference, $tablet]);
    }

    function it_builds_all_variations_on_a_non_localized_reference(
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn(null);

        $reference->addVariation(Argument::any())->shouldBeCalledTimes(3);

        $all = $this->buildAll($reference);
        $all->shouldHaveCount(3);
        $all->shouldBeArrayOfVariations();
    }

    function it_builds_all_variations_on_a_localized_reference(
        $en_US,
        $ecommerce,
        $print,
        $mobile,
        ReferenceInterface $reference
    ) {
        $reference->getLocale()->willReturn($en_US);
        $ecommerce->hasLocale($en_US)->willReturn(true);
        $print->hasLocale($en_US)->willReturn(false);
        $mobile->hasLocale($en_US)->willReturn(true);

        $reference->addVariation(Argument::any())->shouldBeCalledTimes(2);

        $all = $this->buildAll($reference);
        $all->shouldHaveCount(2);
        $all->shouldBeArrayOfVariations();
    }

    public function getMatchers()
    {
        return [
            'beArrayOfVariations' => function ($subject) {
                foreach ($subject as $row) {
                    if (!$row instanceof VariationInterface) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
