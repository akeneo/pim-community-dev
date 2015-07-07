<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;

class PricesDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['pim_catalog_price_collection'],
            'Pim\Bundle\CatalogBundle\Model\ProductPrice'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\PricesDenormalizer');
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

    function it_denormalizes_data_into_price_collection()
    {
        $prices = $this
            ->denormalize(
                [
                    [
                        'data' => 10,
                        'currency' => 'EUR'
                    ],
                    [
                        'data' => 15,
                        'currency' => 'USD'
                    ]
                ],
                'pim_catalog_price_collection',
                'json'
            );

        $prices->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
        $prices->shouldHaveCount(2);

        $prices[0]->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductPriceInterface');
        $prices[0]->getData()->shouldReturn(10);
        $prices[0]->getCurrency()->shouldReturn('EUR');

        $prices[0]->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductPriceInterface');
        $prices[1]->getData()->shouldReturn(15);
        $prices[1]->getCurrency()->shouldReturn('USD');
    }
}
