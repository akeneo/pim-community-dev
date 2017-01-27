<?php

namespace spec\Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\PriceCollection;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PricesDenormalizerSpec extends ObjectBehavior
{
    function let(ProductBuilder $productBuilder, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith(
            ['pim_catalog_price_collection'],
            $productBuilder,
            $normalizer
        );
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes_a_price_collection_from_many_fields(
        $productBuilder,
        $normalizer,
        ProductValueInterface $originalPriceValue,
        ProductValueInterface $firstNewPriceValue,
        ProductValueInterface $secondNewPriceValue,
        ProductInterface $product,
        PriceCollection $priceCollection,
        PriceCollection $updatedPriceCollection,
        AttributeInterface $attribute
    ) {
        $originalPriceValue->getAttribute()->willReturn($attribute);
        $originalPriceValue->getLocale()->willReturn(null);
        $originalPriceValue->getScope()->willReturn(null);
        $originalPriceValue->getPrices()->willReturn($priceCollection);

        $firstNewPriceValue->getAttribute()->willReturn($attribute);
        $firstNewPriceValue->getLocale()->willReturn(null);
        $firstNewPriceValue->getScope()->willReturn(null);
        $firstNewPriceValue->getPrices()->willReturn($updatedPriceCollection);

        $normalizer->normalize($priceCollection, 'standard')->willReturn([
            ['amount' => '21', 'currency' => 'EUR'],
            ['amount' => '42', 'currency' => 'USD'],
        ]);

        $normalizer->normalize($updatedPriceCollection, 'standard')->willReturn([
            ['amount' => '100', 'currency' => 'EUR'],
            ['amount' => '42', 'currency' => 'USD'],
        ]);

        $productBuilder->addProductValue(
            $product,
            $attribute,
            null,
            null,
            [
                ['amount' => '100', 'currency' => 'EUR'],
                ['amount' => '42', 'currency' => 'USD'],
            ]
        )->willReturn($firstNewPriceValue);

        $productBuilder->addProductValue(
            $product,
            $attribute,
            null,
            null,
            [
                ['amount' => '100', 'currency' => 'EUR'],
                ['amount' => '25', 'currency' => 'USD'],
            ]
        )->willReturn($secondNewPriceValue);

        $context = ['value' => $originalPriceValue, 'product' => $product, 'price_currency' => 'EUR'];
        $this->denormalize('100', 'className', null, $context);

        $context = ['value' => $firstNewPriceValue, 'product' => $product, 'price_currency' => 'USD'];
        $this->denormalize('25', 'className', null, $context);
    }

    function it_denormalizes_a_price_collection_from_a_single_field(
        $productBuilder,
        $normalizer,
        PriceCollection $priceCollection,
        ProductValueInterface $originalPriceValue,
        ProductInterface $product,
        ProductValueInterface $newPriceValue,
        AttributeInterface $attribute
    ) {
        $originalPriceValue->getAttribute()->willReturn($attribute);
        $originalPriceValue->getLocale()->willReturn(null);
        $originalPriceValue->getScope()->willReturn(null);
        $originalPriceValue->getPrices()->willReturn($priceCollection);

        $normalizer->normalize($priceCollection, 'standard')->willReturn([
            ['amount' => '120.00', 'currency' => 'EUR'],
            ['amount' => '130.00', 'currency' => 'USD'],
            ['amount' => '110.00', 'currency' => 'CHF'],
        ]);

        $originalPriceValue->getPrices()->willReturn($priceCollection);

        $originalPriceValue->getLocale()->willReturn(null);
        $originalPriceValue->getScope()->willReturn(null);

        $productBuilder->addProductValue(
            $product,
            $attribute,
            null,
            null,
            [
                ['amount' => '120.00', 'currency' => 'EUR'],
                ['amount' => '145.40', 'currency' => 'USD'],
                ['amount' => '100.00', 'currency' => 'CHF'],
            ]
        )->willReturn($newPriceValue);

        $context = ['value' => $originalPriceValue, 'product' => $product, 'price_currency' => 'WillNotBeUsed'];
        $this->denormalize('120.00 EUR, 145.40 USD, 100 CHF', 'className', null, $context);
    }

    function it_returns_a_price_collection_even_if_the_data_is_empty(
        $productBuilder,
        $normalizer,
        PriceCollection $priceCollection,
        PriceCollection $emptyPriceCollection,
        ProductValueInterface $originalPriceValue,
        ProductInterface $product,
        ProductValueInterface $newPriceValue,
        AttributeInterface $attribute
    ) {
        $originalPriceValue->getAttribute()->willReturn($attribute);
        $originalPriceValue->getLocale()->willReturn(null);
        $originalPriceValue->getScope()->willReturn(null);
        $originalPriceValue->getPrices()->willReturn($priceCollection);

        $normalizer->normalize($priceCollection, 'standard')->willReturn([
            ['amount' => '120.00', 'currency' => 'EUR'],
            ['amount' => '130.00', 'currency' => 'USD'],
            ['amount' => '110.00', 'currency' => 'CHF'],
        ]);

        $originalPriceValue->getPrices()->willReturn($priceCollection);

        $originalPriceValue->getLocale()->willReturn(null);
        $originalPriceValue->getScope()->willReturn(null);

        $productBuilder->addProductValue(
            $product,
            $attribute,
            null,
            null,
            null
        )->willReturn($newPriceValue);

        $newPriceValue->getPrices()->willReturn($emptyPriceCollection);

        $context = ['value' => $originalPriceValue, 'product' => $product, 'price_currency' => 'WillNotBeUsed'];
        $this->denormalize('', 'className', null, $context)->shouldReturn($emptyPriceCollection);
        $this->denormalize(null, 'className', null, $context)->shouldReturn($emptyPriceCollection);
    }
}
