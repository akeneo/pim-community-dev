<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Standard\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\PriceFactory;
use Pim\Component\Catalog\Model\PriceCollection;
use Pim\Component\Catalog\Model\ProductPrice;
use Pim\Component\Catalog\Model\ProductPriceInterface;

class PricesDenormalizerSpec extends ObjectBehavior
{
    function let(PriceFactory $priceFactory)
    {
        $this->beConstructedWith(['pim_catalog_price_collection'], $priceFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Standard\ProductValue\PricesDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_price_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'standard')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_price_collection', 'standard')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_price_collection', 'standard')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_price_collection', 'standard')->shouldReturn(null);
    }

    function it_denormalizes_data_into_price_collection(
        $priceFactory,
        ProductPrice $priceEUR,
        ProductPrice $priceUSD
    ) {
        $priceFactory->createPrice(10, 'EUR')->willReturn($priceEUR);
        $priceFactory->createPrice(15.45, 'USD')->willReturn($priceUSD);

        $prices = $this
            ->denormalize(
                [
                    [
                        'amount'   => 10,
                        'currency' => 'EUR',
                    ],
                    [
                        'amount'   => 15.45,
                        'currency' => 'USD',
                    ]
                ],
                'pim_catalog_price_collection',
                'standard',
                []
            );

        $prices->shouldHaveType(PriceCollection::class);
        $prices->shouldHaveCount(2);

        $prices->get(0)->shouldReturn($priceEUR);
        $prices->get(1)->shouldReturn($priceUSD);
    }
}
