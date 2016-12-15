<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Normalizer\Standard\LocaleNormalizer as LocaleNormalizerStandard;
use Prophecy\Argument;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(LocaleNormalizerStandard $localeNormalizerStandard)
    {
        $this->beConstructedWith($localeNormalizerStandard);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\LocaleNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_locale_normalization_into_flat(LocaleInterface $locale)
    {
        $this->supportsNormalization($locale, 'flat')->shouldBe(true);
        $this->supportsNormalization($locale, 'csv')->shouldBe(false);
        $this->supportsNormalization($locale, 'json')->shouldBe(false);
        $this->supportsNormalization($locale, 'xml')->shouldBe(false);
    }

    function it_normalizes_locales(
        LocaleNormalizerStandard $localeNormalizerStandard,
        LocaleInterface $locale
    ) {
        $localeNormalizerStandard->supportsNormalization($locale, 'standard')->willReturn(true);
        $localeNormalizerStandard->normalize($locale, 'standard', [])->willReturn(
            [
                'code'      => 'locale_code',
                'activated' => false,
            ]
        );

        $this->normalize($locale, 'flat')->shouldReturn(
            [
                'code'      => 'locale_code',
                'activated' => false,
            ]
        );
    }
}
