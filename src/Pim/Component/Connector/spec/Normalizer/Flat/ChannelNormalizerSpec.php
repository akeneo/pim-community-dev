<?php

namespace spec\Pim\Component\Connector\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

class ChannelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\Flat\ChannelNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_channel_normalization_into_csv(ChannelInterface $channel)
    {
        $this->supportsNormalization($channel, 'csv')->shouldBe(true);
        $this->supportsNormalization($channel, 'flat')->shouldBe(true);
        $this->supportsNormalization($channel, 'json')->shouldBe(false);
        $this->supportsNormalization($channel, 'xml')->shouldBe(false);
    }

    function it_normalizes_channel(
        ChannelInterface $channel,
        CurrencyInterface $eur,
        CurrencyInterface $usd,
        LocaleInterface $en,
        LocaleInterface $fr,
        CategoryInterface $category
    ) {
        $channel->getCode()->willReturn('ecommerce');
        $channel->getLabel()->willReturn('Ecommerce');
        $channel->getColor()->willReturn('red');
        $channel->getCurrencies()->willReturn([$eur, $usd]);
        $eur->getCode()->willReturn('EUR');
        $usd->getCode()->willReturn('USD');
        $channel->getLocales()->willReturn([$en, $fr]);
        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
        $channel->getCategory()->willReturn($category);
        $category->getCode()->willReturn('Master catalog');
        $channel->getConversionUnits()->willReturn(
            [
                'Weight' => 'Kilogram',
                'Size' => 'Centimeter'
            ]
        );

        $this->normalize($channel)->shouldReturn(
            [
                'code'       => 'ecommerce',
                'label'      => 'Ecommerce',
                'currencies' => 'EUR,USD',
                'locales'    => 'en_US,fr_FR',
                'tree'       => 'Master catalog',
                'color'      => 'red',
            ]
        );
    }
}
