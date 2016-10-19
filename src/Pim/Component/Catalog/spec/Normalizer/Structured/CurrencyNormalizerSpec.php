<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Normalizer\Structured\CurrencyNormalizer;

class CurrencyNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CurrencyNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_locales(CurrencyInterface $currency)
    {
        $this->supportsNormalization($currency, 'csv')->shouldBe(false);
        $this->supportsNormalization($currency, 'json')->shouldBe(true);
        $this->supportsNormalization($currency, 'xml')->shouldBe(true);
    }

    function it_normalizes_locales(CurrencyInterface $currency)
    {
        $currency->getCode()->willReturn('EUR');
        $currency->isActivated()->willReturn(true);

        $this->normalize($currency, 'json')->shouldReturn([
            'code'      => 'EUR',
            'activated' => true,
        ]);
    }
}
