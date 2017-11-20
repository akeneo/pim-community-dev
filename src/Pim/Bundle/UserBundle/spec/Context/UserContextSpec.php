<?php

namespace spec\Pim\Bundle\UserBundle\Context;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserContextSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        TokenInterface $token,
        User $user,
        LocaleInterface $en,
        LocaleInterface $fr,
        LocaleInterface $de,
        ChannelInterface $ecommerce,
        ChannelInterface $mobile,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryRepositoryInterface $productCategoryRepo,
        RequestStack $requestStack,
        ChoicesBuilderInterface $choicesBuilder,
        Request $request,
        SessionInterface $session
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $requestStack->getCurrentRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $request->hasSession()->willReturn(true);

        $en->getCode()->willReturn('en_US');
        $fr->getCode()->willReturn('fr_FR');
        $de->getCode()->willReturn('de_DE');

        $en->isActivated()->willReturn(true);
        $fr->isActivated()->willReturn(true);
        $de->isActivated()->willReturn(true);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($en);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($fr);
        $localeRepository->findOneByIdentifier('de_DE')->willReturn($de);

        $localeRepository->getActivatedLocales()->willReturn([$en, $fr, $de]);
        $channelRepository->findOneByIdentifier([])->willReturn($mobile);
        $productCategoryRepo->getTrees()->willReturn([$firstTree, $secondTree]);

        $this->beConstructedWith(
            $tokenStorage,
            $localeRepository,
            $channelRepository,
            $productCategoryRepo,
            $requestStack,
            $choicesBuilder,
            'en_US'
        );
    }

    function it_provides_locale_from_the_request_if_it_has_been_set($request, $fr, $session)
    {
        $request->get('dataLocale')->willReturn('fr_FR');

        $session->set('dataLocale', 'fr_FR')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentLocale()->shouldReturn($fr);
    }

    function it_provides_user_session_locale_if_locale_is_not_present_in_request(
        $request,
        $session,
        $fr
    ) {
        $request->get('dataLocale')->willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');

        $session->set('dataLocale', 'fr_FR')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentLocale()->shouldReturn($fr);
    }

    function it_provides_user_locale_if_locale_is_not_present_in_user_session(
        User $user,
        $request,
        $session,
        $de
    ) {
        $request->get('dataLocale')->willReturn(null);
        $session->get('dataLocale')->willReturn(null);
        $user->getCatalogLocale()->willReturn($de);

        $session->set('dataLocale', 'de_DE')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentLocale()->shouldReturn($de);
    }

    function it_provides_first_activated_locale_if_locale_is_not_present_in_request_and_user_properties(
        User $user,
        $request,
        $session,
        $en
    ) {
        $request->get('dataLocale')->willReturn(null);
        $session->get('dataLocale')->willReturn(null);

        $session->set('dataLocale', 'en_US')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $user->getCatalogLocale()->willReturn(null);

        $this->getCurrentLocale()->shouldReturn($en);
    }

    function it_throws_an_exception_if_there_are_no_activated_locales(
        User $user,
        $request,
        $session,
        $localeRepository
    ) {
        $request->get('dataLocale')->willReturn(null);
        $session->get('dataLocale')->willReturn(null);
        $user->getCatalogLocale()->willReturn(null);

        $localeRepository->findOneByIdentifier('en_US')->willReturn(null);
        $localeRepository->getActivatedLocales()->willReturn([]);

        $this
            ->shouldThrow(new \Exception('There are no activated locales'))
            ->duringGetCurrentLocale();
    }

    function its_get_current_locale_code_method_returns_a_locale_code(
        User $user,
        $request,
        $session
    ) {
        $request->get('dataLocale')->willReturn(null);
        $session->get('dataLocale')->willReturn(null);
        $user->getCatalogLocale()->willReturn(null);


        $session->set('dataLocale', 'en_US')->shouldBeCalled();
        $session->save()->shouldBeCalled();

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

    function its_get_user_tree_method_returns_user_tree_if_available(User $user, $secondTree)
    {
        $user->getDefaultTree()->willReturn($secondTree);
        $this->getUserProductCategoryTree()->shouldReturn($secondTree);
    }

    function its_get_user_tree_method_returns_the_first_available_tree_if_user_tree_is_not_available($firstTree)
    {
        $this->getUserProductCategoryTree()->shouldReturn($firstTree);
    }

    function it_builds_user_product_context_for_normalization(
        $tokenStorage,
        $localeRepository,
        $channelRepository,
        $choicesBuilder,
        ChannelInterface $userChannel,
        ChannelInterface $ecommerce,
        LocaleInterface $fr,
        LocaleInterface $en,
        TokenInterface $token,
        UserInterface $user
    ) {
        $userChannel->getCode()->willReturn('mobile');
        $channelRepository->findOneByIdentifier([])->willReturn($userChannel);
        $tokenStorage->getToken()->willReturn($token);

        $token->getUser()->willReturn($user);

        $channelRepository->findAll()->willReturn([$ecommerce]);
        $choicesBuilder->buildChoices([$ecommerce])->willReturn([
            'mobile' => $userChannel,
            'ecommerce' => $ecommerce
        ]);

        $fr->getCode()->willReturn('fr_FR');
        $en->getCode()->willReturn('en_US');

        $localeRepository->getActivatedLocales()->willReturn([$en, $fr]);

        $user->getUiLocale()->willReturn($fr);
        $user->getCatalogScope()->willReturn($userChannel);

        $this->toArray()->shouldReturn([
            'locales'  => ['en_US', 'fr_FR'],
            'channels' => ['mobile', 'ecommerce'],
            'locale'   => 'fr_FR'
        ]);
    }
}

class User
{
    function getCatalogLocale()
    {
    }

    function getCatalogScope()
    {
    }

    function getDefaultTree()
    {
    }
}
