<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\PriceNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PriceNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PriceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_prices_only(ProductPriceInterface $price)
    {
        $this->supportsNormalization($price, 'standard')->shouldReturn(true);
        $this->supportsNormalization($price, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_price_in_standard_format_only_with_decimal_allowed(ProductPriceInterface $price)
    {
        $price->getCurrency()->willReturn('EUR');
        $price->getData()->willReturn('125.99');

        $this->normalize($price, 'standard', ['is_decimals_allowed' => true])->shouldReturn([
            'amount'   => '125.99',
            'currency' => 'EUR',
        ]);
    }

    function it_normalizes_price_in_standard_format_only_with_decimal_disallowed(ProductPriceInterface $price)
    {
        $price->getCurrency()->willReturn('USD');
        $price->getData()->willReturn('125.00');

        $this->normalize($price, 'standard', ['is_decimals_allowed' => false])->shouldReturn([
            'amount'   => 125,
            'currency' => 'USD'
        ]);
    }

    function it_returns_data_if_it_is_not_a_numeric(ProductPriceInterface $price)
    {
        $price->getCurrency()->willReturn('EUR');
        $price->getData()->willReturn('a_price_data');

        $this->normalize($price, 'standard', ['is_decimals_allowed' => false])->shouldReturn([
            'amount'   => 'a_price_data',
            'currency' => 'EUR'
        ]);
    }
}
