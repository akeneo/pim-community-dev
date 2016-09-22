<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard\Product;

use Pim\Component\Catalog\Model\ProductPriceInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PriceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\Product\PriceNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_format_and_prices_only(ProductPriceInterface $price)
    {
        $otherObject = [];

        $this->supportsNormalization($price, 'standard')->shouldReturn(true);
        $this->supportsNormalization($price, 'other_format')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'standard')->shouldReturn(false);
        $this->supportsNormalization($otherObject, 'other_format')->shouldReturn(false);
    }

    function it_normalizes_price_in_standard_format_only(ProductPriceInterface $price)
    {
        $price->getCurrency()->willReturn('EUR');
        $price->getData()->willReturn('125.99');

        $this->normalize($price, 'standard')->shouldReturn([
            'amount'   => '125.99',
            'currency' => 'EUR',
        ]);
    }
}

