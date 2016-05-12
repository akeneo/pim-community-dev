<?php

namespace spec\Pim\Component\Connector\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CurrencyInterface;

class CurrencyNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\Flat\CurrencyNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_currency_normalization_into_csv(CurrencyInterface $currency)
    {
        $this->supportsNormalization($currency, 'csv')->shouldBe(true);
        $this->supportsNormalization($currency, 'flat')->shouldBe(true);
        $this->supportsNormalization($currency, 'json')->shouldBe(false);
        $this->supportsNormalization($currency, 'xml')->shouldBe(false);
    }

    function it_normalizes_currency(
        CurrencyInterface $currency
    ) {
        $currency->getCode()->willReturn('EUR');
        $currency->isActivated()->willReturn(true);

        $this->normalize($currency)->shouldReturn(
            [
                'code'      => 'EUR',
                'activated' => 1,
            ]
        );
    }
}
