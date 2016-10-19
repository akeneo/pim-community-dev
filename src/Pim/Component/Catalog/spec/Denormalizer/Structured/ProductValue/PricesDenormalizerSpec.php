<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Structured\ProductValue;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;

class PricesDenormalizerSpec extends ObjectBehavior
{
    function let(LocalizerInterface $localizer)
    {
        $this->beConstructedWith(
            ['pim_catalog_price_collection'],
            $localizer,
            'Pim\Component\Catalog\Model\ProductPrice'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Structured\ProductValue\PricesDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_price_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_price_collection', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_price_collection', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_price_collection', 'json')->shouldReturn(null);
    }

    function it_denormalizes_data_into_price_collection_with_en_US_locale($localizer)
    {
        $context = ['locale' => 'en_US'];

        $localizer->localize(10, $context)->willReturn(10);
        $localizer->localize(15.45, $context)->willReturn(15.45);

        $prices = $this
            ->denormalize(
                [
                    [
                        'amount'   => 10,
                        'currency' => 'EUR'
                    ],
                    [
                        'amount'   => 15.45,
                        'currency' => 'USD'
                    ]
                ],
                'pim_catalog_price_collection',
                'json',
                $context
            );

        $prices->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
        $prices->shouldHaveCount(2);

        $prices[0]->shouldBeAnInstanceOf('Pim\Component\Catalog\Model\ProductPriceInterface');
        $prices[0]->getData()->shouldReturn(10);
        $prices[0]->getCurrency()->shouldReturn('EUR');

        $prices[0]->shouldBeAnInstanceOf('Pim\Component\Catalog\Model\ProductPriceInterface');
        $prices[1]->getData()->shouldReturn(15.45);
        $prices[1]->getCurrency()->shouldReturn('USD');
    }

    function it_denormalizes_data_into_price_collection_with_fr_FR_locale($localizer)
    {
        $context = ['locale' => 'fr_FR'];

        $localizer->localize(10, $context)->willReturn(10);
        $localizer->localize(15.45, $context)->willReturn('15,45');

        $prices = $this
            ->denormalize(
                [
                    [
                        'amount'   => 10,
                        'currency' => 'EUR'
                    ],
                    [
                        'amount'   => 15.45,
                        'currency' => 'USD'
                    ]
                ],
                'pim_catalog_price_collection',
                'json',
                $context
            );

        $prices->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
        $prices->shouldHaveCount(2);

        $prices[0]->shouldBeAnInstanceOf('Pim\Component\Catalog\Model\ProductPriceInterface');
        $prices[0]->getData()->shouldReturn(10);
        $prices[0]->getCurrency()->shouldReturn('EUR');

        $prices[0]->shouldBeAnInstanceOf('Pim\Component\Catalog\Model\ProductPriceInterface');
        $prices[1]->getData()->shouldReturn('15,45');
        $prices[1]->getCurrency()->shouldReturn('USD');
    }
}
