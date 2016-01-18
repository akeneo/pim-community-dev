<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;

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

    function it_denormalizes_a_price_collection_from_many_fields(
        ProductValueInterface $priceValue,
        $productBuilder,
        ProductPriceInterface $productPriceEur,
        ProductPriceInterface $productPriceUsd
    ) {
        $productBuilder->addPriceForCurrency($priceValue, 'EUR')
            ->willReturn($productPriceEur)
            ->shouldBeCalled();
        $productPriceEur->setCurrency('EUR')
            ->shouldBeCalled();
        $productPriceEur->setData('100')
            ->shouldBeCalled();
        $priceValue->addPrice($productPriceEur)
            ->shouldBeCalled();

        $productBuilder->addPriceForCurrency($priceValue, 'USD')
            ->willReturn($productPriceUsd)
            ->shouldBeCalled();
        $productPriceUsd->setCurrency('USD')
            ->shouldBeCalled();
        $productPriceUsd->setData('25')
            ->shouldBeCalled();
        $priceValue->addPrice($productPriceUsd)
            ->shouldBeCalled();

        $priceValue->getPrices()->shouldBeCalled();

        $context = ['value' => $priceValue, 'price_currency' => 'EUR'];
        $this->denormalize('100', 'className', null, $context);

        $context = ['value' => $priceValue, 'price_currency' => 'USD'];
        $this->denormalize('25', 'className', null, $context);
    }

    function it_denormalizes_a_price_collection_from_a_single_field(
        ProductValueInterface $priceValue,
        $productBuilder,
        ProductPriceInterface $productPriceEur,
        ProductPriceInterface $productPriceUsd
    ) {
        $productBuilder->addPriceForCurrency($priceValue, 'EUR')
            ->willReturn($productPriceEur)
            ->shouldBeCalled();
        $productPriceEur->setCurrency('EUR')
            ->shouldBeCalled();
        $productPriceEur->setData('120.00')
            ->shouldBeCalled();
        $priceValue->addPrice($productPriceEur)
            ->shouldBeCalled();

        $productBuilder->addPriceForCurrency($priceValue, 'USD')
            ->willReturn($productPriceUsd)
            ->shouldBeCalled();
        $productPriceUsd->setCurrency('USD')
            ->shouldBeCalled();
        $productPriceUsd->setData('145.40')
            ->shouldBeCalled();
        $priceValue->addPrice($productPriceUsd)
            ->shouldBeCalled();

        $productBuilder->addPriceForCurrency($priceValue, 'CHF')
            ->willReturn($productPriceUsd)
            ->shouldBeCalled();
        $productPriceUsd->setCurrency('CHF')
            ->shouldBeCalled();
        $productPriceUsd->setData('100')
            ->shouldBeCalled();
        $priceValue->addPrice($productPriceUsd)
            ->shouldBeCalled();

        $priceValue->getPrices()->shouldBeCalled();

        $context = ['value' => $priceValue, 'price_currency' => 'WillNotBeUsed'];
        $this->denormalize('120.00 EUR, 145.40 USD, 100 CHF', 'className', null, $context);
    }

    function it_returns_a_price_collection_even_if_the_data_is_empty(
        $productBuilder,
        ProductValueInterface $priceValue,
        ProductPriceInterface $price,
        ArrayCollection $priceCollection
    ) {
        $productBuilder->addPriceForCurrency(Argument::cetera())->willReturn($price);
        $priceValue->addPrice($price)->shouldBeCalled();
        $priceValue->getPrices()->willReturn($priceCollection);

        $context = ['value' => $priceValue, 'price_currency' => 'WillNotBeUsed'];
        $this->denormalize('', 'className', null, $context)->shouldReturn($priceCollection);
        $this->denormalize(null, 'className', null, $context)->shouldReturn($priceCollection);
    }
}
