<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

class PricesDenormalizerSpec extends ObjectBehavior
{
    function let(ProductBuilder $productBuilder)
    {
        $this->beConstructedWith(
            ['pim_catalog_price_collection'],
            $productBuilder
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_a_price_collection(AbstractProductValue $priceValue, $productBuilder, ProductPriceInterface $productPrice)
    {
        $context = ['value' => $priceValue, 'price_currency' => 'EUR'];

        $productBuilder->addPriceForCurrency($priceValue, 'EUR')->willReturn($productPrice);
        $productPrice->setCurrency('EUR')->shouldBeCalled();
        $productPrice->setData('100')->shouldBeCalled();

        $this->denormalize('100', 'className', null, $context)->shouldReturn($productPrice);
    }

    function it_returns_null_if_the_data_is_empty(AbstractProductValue $productValueInterface)
    {
        $this->denormalize('', 'className', null, [])->shouldReturn(null);
        $this->denormalize(null, 'className', null, [])->shouldReturn(null);
    }
}
