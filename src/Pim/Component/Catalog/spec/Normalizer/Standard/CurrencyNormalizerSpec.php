<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CurrencyInterface;

class CurrencyNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\CurrencyNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(CurrencyInterface $currency)
    {
        $this->supportsNormalization($currency, 'standard')->shouldBe(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization($currency, 'json')->shouldBe(false);
        $this->supportsNormalization($currency, 'xml')->shouldBe(false);
    }

    function it_normalizes_currency(CurrencyInterface $currency)
    {
        $currency->getCode()->willReturn('EUR');
        $currency->isActivated()->willReturn(true);

        $this->normalize($currency, 'standard')->shouldReturn([
            'code'    => 'EUR',
            'enabled' => true,
        ]);
    }
}
