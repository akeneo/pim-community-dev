<?php

namespace spec\Pim\Bundle\UserBundle\Context;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;

class UserContextSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        TokenInterface $token,
        User $user,
        Locale $en,
        Locale $fr,
        Locale $de
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $en->getCode()->willReturn('en_EN');
        $fr->getCode()->willReturn('fr_FR');
        $de->getCode()->willReturn('de_DE');

        $en->isActivated()->willReturn(true);
        $fr->isActivated()->willReturn(true);
        $de->isActivated()->willReturn(true);

        $localeManager->getLocaleByCode('en_EN')->willReturn($en);
        $localeManager->getLocaleByCode('fr_FR')->willReturn($fr);
        $localeManager->getLocaleByCode('de_DE')->willReturn($de);

        $localeManager->getActiveLocales()->willReturn([$en, $fr, $de]);

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $this->beConstructedWith($securityContext, $securityFacade, $localeManager, $channelManager);
    }

    function it_provides_locale_from_the_request_if_it_has_been_set(Request $request, $fr)
    {
        $request->get('dataLocale')->willReturn('fr_FR');

        $this->setRequest($request);
        $this->getCurrentLocale()->shouldReturn($fr);
    }

    function it_provides_user_locale_if_locale_is_not_present_in_request(User $user, $de)
    {
        $user->getCatalogLocale()->willReturn($de);
        $this->getCurrentLocale()->shouldReturn($de);
    }

    function it_provides_first_activated_locale_if_locale_is_not_present_in_request_and_user_properties($en)
    {
        $this->getCurrentLocale()->shouldReturn($en);
    }
}

class User
{
    public function getCatalogLocale()
    {
    }
}
