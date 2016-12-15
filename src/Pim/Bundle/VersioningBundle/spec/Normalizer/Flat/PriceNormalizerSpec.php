<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductPriceInterface;

class PriceNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_product_price(ProductPriceInterface $price)
    {
        $this->supportsNormalization($price, 'flat')->shouldBe(true);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_price(ProductPriceInterface $price)
    {
        $price->getData()->willReturn(25.3);
        $price->getCurrency()->willReturn('EUR');

        $this->normalize($price, null, ['field_name' => 'price'])->shouldReturn(['price-EUR' => '25.30']);
    }

    function it_normalizes_null_price(ProductPriceInterface $price)
    {
        $price->getData()->willReturn(null);
        $price->getCurrency()->willReturn('EUR');

        $this->normalize($price, null, ['field_name' => 'price'])->shouldReturn(['price-EUR' => '']);
    }

    function it_normalizes_empty_price(ProductPriceInterface $price)
    {
        $price->getData()->willReturn('');
        $price->getCurrency()->willReturn('EUR');

        $this->normalize($price, null, ['field_name' => 'price'])->shouldReturn(['price-EUR' => '']);
    }

    function it_throws_exception_when_the_context_field_name_key_is_not_provided()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('Missing required "field_name" context value, got "foo, bar"'))
            ->duringNormalize(false, null, ['foo' => true, 'bar' => true]);
    }
}
