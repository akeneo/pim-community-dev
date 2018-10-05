<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\Standard;

use Akeneo\Channel\Component\Normalizer\Standard\CurrencyNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\CurrencyInterface;

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
