<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\ProductValuesNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_standard_format_and_collection_values()
    {
        $collection = new ArrayCollection();

        $this->supportsNormalization($collection, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($collection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values_in_standard_format(
        $serializer,
        ProductValueInterface $textValue,
        AttributeInterface $text,
        ProductValueInterface $priceValue,
        AttributeInterface $price
    ) {
        $textValue->getAttribute()->willReturn($text);
        $priceValue->getAttribute()->willReturn($price);
        $text->getCode()->willReturn('text');
        $price->getCode()->willReturn('price');

        $serializer
            ->normalize($textValue, 'standard', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => null, 'scope' => null, 'value' => 'foo']);

        $serializer
            ->normalize($priceValue, 'standard', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => [
                ['amount' => '12.50', 'currency' => 'USD'],
                ['amount' => '15.00', 'currency' => 'EUR']
            ]]);

        $this
            ->normalize([$textValue, $priceValue], 'standard')
            ->shouldReturn(
                [
                    'text' => [
                        ['locale' => null, 'scope' => null, 'value' => 'foo']
                    ],
                    'price' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => [
                            ['amount' => '12.50', 'currency' => 'USD'],
                            ['amount' => '15.00', 'currency' => 'EUR']
                        ]]
                    ]
                ]
            );
    }
}
