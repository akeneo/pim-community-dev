<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(LocaleHelper $localeHelper)
    {
        $this->beConstructedWith($localeHelper);
    }

    function it_supports_locales(LocaleInterface $en)
    {
        $this->supportsNormalization($en, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_locales($localeHelper, LocaleInterface $en)
    {
        $en->getCode()->willReturn('en_US');
        $localeHelper->getLocaleLabel('en_US')->willReturn('English (America)');
        $localeHelper->getDisplayRegion('en_US')->willReturn('America');
        $localeHelper->getDisplayLanguage('en_US')->willReturn('English');

        $this->normalize($en, 'internal_api')->shouldReturn([
            'code'     => 'en_US',
            'label'    => 'English (America)',
            'region'   => 'America',
            'language' => 'English'
        ]);
    }
}
