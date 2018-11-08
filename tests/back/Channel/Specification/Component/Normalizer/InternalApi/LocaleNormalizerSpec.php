<?php

namespace Specification\Akeneo\Channel\Component\Normalizer\InternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\Channel\Component\Model\LocaleInterface;

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
        $userContext->getUiLocale()->willReturn($en);

        $this->normalize($en, 'internal_api')->shouldReturn([
            'code'     => 'en_US',
            'label'    => 'English (United States)',
            'region'   => 'United States',
            'language' => 'English'
        ]);
    }
}
