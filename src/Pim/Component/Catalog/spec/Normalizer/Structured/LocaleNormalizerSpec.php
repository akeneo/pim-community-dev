<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_locales(LocaleInterface $locale)
    {
        $this->supportsNormalization($locale, 'csv')->shouldBe(false);
        $this->supportsNormalization($locale, 'json')->shouldBe(true);
        $this->supportsNormalization($locale, 'xml')->shouldBe(true);
    }

    function it_normalizes_locales(LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('en_US');
        $locale->isActivated()->willReturn(true);

        $this->normalize($locale, 'json')->shouldReturn([
            'code' => 'en_US',
            'activated' => true,
        ]);
    }
}
