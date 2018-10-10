<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\Standard;

use Akeneo\Channel\Component\Normalizer\Standard\LocaleNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(LocaleInterface $locale)
    {
        $this->supportsNormalization($locale, 'standard')->shouldBe(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldBe(false);
        $this->supportsNormalization($locale, 'json')->shouldBe(false);
        $this->supportsNormalization($locale, 'xml')->shouldBe(false);
    }

    function it_normalizes_locale(LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('en_US');
        $locale->isActivated()->willReturn(true);

        $this->normalize($locale, 'standard')->shouldReturn([
            'code'    => 'en_US',
            'enabled' => true,
        ]);
    }
}
