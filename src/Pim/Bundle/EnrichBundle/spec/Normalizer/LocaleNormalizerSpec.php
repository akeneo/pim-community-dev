<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\LocaleInterface;

class LocaleNormalizerSpec extends ObjectBehavior
{
    function let(UserContext $userContext)
    {
        $this->beConstructedWith($userContext);
    }

    function it_supports_locales(LocaleInterface $en)
    {
        $this->supportsNormalization($en, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_locales($userContext, LocaleInterface $en)
    {
        $en->getCode()->willReturn('en_US');
        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->normalize($en, 'internal_api')->shouldReturn([
            'code'     => 'en_US',
            'label'    => 'English (United States)',
            'region'   => 'United States',
            'language' => 'English'
        ]);
    }
}
