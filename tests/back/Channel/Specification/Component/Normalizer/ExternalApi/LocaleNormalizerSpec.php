<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Normalizer\ExternalApi\LocaleNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleNormalizer::class);
    }

    function it_supports_a_locale(LocaleInterface $locale)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($locale, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($locale, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_locale($stdNormalizer, LocaleInterface $locale)
    {
        $data = ['code' => 'en_US'];

        $stdNormalizer->normalize($locale, 'standard', [])->willReturn($data);

        $this->normalize($locale, 'external_api', [])->shouldReturn($data);
    }
}
