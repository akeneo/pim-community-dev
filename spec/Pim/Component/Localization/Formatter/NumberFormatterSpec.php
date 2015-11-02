<?php

namespace spec\Pim\Component\Localization\Formatter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NumberFormatterSpec extends ObjectBehavior
{
    function let(TokenStorage $tokenStorage)
    {
        $this->beConstructedWith($tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Localization\Formatter\NumberFormatter');
    }

    function it_returns_english_integer(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('en');

        $this->format('5000')->shouldReturn('5,000');
    }

    function it_returns_english_decimal(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('en');

        $this->format('5000.1')->shouldReturn('5,000.1');
    }

    function it_returns_english_decimal_with_digits(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('en');

        $this->format('5000.1000')->shouldReturn('5,000.1000');
    }

    function it_returns_french_integer(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('fr');

        $this->format('5000')->shouldReturn('5 000');
    }

    function it_returns_french_decimal(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('fr');

        $this->format('5000.1')->shouldReturn('5 000,1');
    }

    function it_returns_french_decimal_with_digits(
        $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $uiLocale
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($uiLocale);
        $uiLocale->getLanguage()->willReturn('fr');

        $this->format('5000.1000')->shouldReturn('5 000,1000');
    }
}
