<?php

namespace spec\Pim\Component\Api\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\CurrencyNormalizer;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CurrencyNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CurrencyNormalizer::class);
    }

    function it_supports_a_currency(CurrencyInterface $currency)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($currency, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($currency, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_currency($stdNormalizer, CurrencyInterface $currency)
    {
        $data = ['code' => 'EUR', 'enabled' => true];

        $stdNormalizer->normalize($currency, 'standard', [])->willReturn($data);

        $this->normalize($currency, 'external_api', [])->shouldReturn($data);
    }
}
