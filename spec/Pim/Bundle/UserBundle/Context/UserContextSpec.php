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
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Category;

class UserContextSpec extends ObjectBehavior
{
    function let(
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        CategoryManager $categoryManager,
        TokenInterface $token,
        User $user,
        Locale $en,
        Locale $fr,
        Locale $de,
        Channel $ecommerce,
        Channel $mobile,
        Category $firstTree,
        Category $secondTree
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
        $de->getCode()->willReturn('de_DE');

        $en->isActivated()->willReturn(true);
        $fr->isActivated()->willReturn(true);
        $de->isActivated()->willReturn(true);

        $localeManager->getLocaleByCode('en_US')->willReturn($en);
        $localeManager->getLocaleByCode('fr_FR')->willReturn($fr);
        $localeManager->getLocaleByCode('de_DE')->willReturn($de);

        $localeManager->getActiveLocales()->willReturn([$en, $fr, $de]);
        $channelManager->getChannels()->willReturn([$mobile, $ecommerce]);
        $categoryManager->getTrees()->willReturn([$firstTree, $secondTree]);

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $this->beConstructedWith($securityContext, $securityFacade, $localeManager, $channelManager, $categoryManager, 'en_US');
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

    function it_throws_an_exception_if_user_doesnt_have_access_to_any_activated_locales($securityFacade)
    {
        $securityFacade->isGranted(Argument::any())->willReturn(false);

        $this->shouldThrow(new \Exception("User doesn't have access to any activated locales"))->duringGetCurrentLocale();
    }

    function its_get_current_locale_code_method_returns_a_locale_code()
    {
        $this->getCurrentLocaleCode()->shouldReturn('en_US');
    }

    function its_get_user_locales_method_returns_all_locales_available_to_the_user($en, $fr, $de)
    {
        $this->getUserLocales()->shouldReturn([$en, $fr, $de]);
    }

    function its_get_user_locale_codes_method_returns_all_locale_codes_available_to_the_user()
    {
        $this->getUserLocaleCodes()->shouldReturn(['en_US', 'fr_FR', 'de_DE']);
    }

    function its_get_user_channel_method_returns_user_channel_if_available(User $user, $ecommerce)
    {
        $user->getCatalogScope()->willReturn($ecommerce);
        $this->getUserChannel()->shouldReturn($ecommerce);
    }

    function its_get_user_channel_method_returns_the_first_available_channel_if_user_channel_is_not_available($mobile)
    {
        $this->getUserChannel()->shouldReturn($mobile);
    }

    function its_get_user_channel_code_method_returns_a_channel_code($mobile)
    {
        $mobile->getCode()->willReturn('mobile');
        $this->getUserChannelCode()->shouldReturn('mobile');
    }

    function its_get_user_tree_method_returns_user_tree_if_available(User $user, $secondTree)
    {
        $user->getDefaultTree()->willReturn($secondTree);
        $this->getUserTree()->shouldReturn($secondTree);
    }

    function its_get_user_tree_method_returns_the_first_available_tree_if_user_tree_is_not_available($firstTree)
    {
        $this->getUserTree()->shouldReturn($firstTree);
    }
}

class User
{
    public function getCatalogLocale()
    {
    }

    public function getCatalogScope()
    {
    }

    public function getDefaultTree()
    {
    }
}
