<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Context;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserContextSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\UserBundle\Context\UserContext');
    }

    function let(
        TokenStorageInterface $tokenStorage,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        RequestStack $requestStack,
        ChoicesBuilderInterface $choicesBuilder,
        AuthorizationCheckerInterface $authorizationChecker,
        CategoryAccessRepository $categoryAccessRepo,
        TokenInterface $token,
        UserInterface $user,
        Request $request
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $tokenStorage,
            $localeRepository,
            $channelRepository,
            $categoryRepository,
            $requestStack,
            $choicesBuilder,
            $authorizationChecker,
            $categoryAccessRepo,
            'en_US',
            'defaultTree'
        );
    }

    function it_gets_the_default_tree_if_accessible($user, $authorizationChecker, CategoryInterface $secondTree)
    {
        $user->getDefaultTree()->willReturn($secondTree);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $secondTree)->willReturn(true);

        $this->getAccessibleUserTree()->shouldReturn($secondTree);
    }

    function it_gets_the_first_accessible_tree_if_the_default_user_tree_is_not_accessible(
        $user,
        $authorizationChecker,
        $categoryRepository,
        $categoryAccessRepo,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree
    ) {
        $user->getDefaultTree()->willReturn($secondTree);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $secondTree)->willReturn(false);

        $grantedTrees = [$thirdTree, $firstTree];

        $categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([1]);
        $categoryRepository->getGrantedTrees([1])->willReturn($grantedTrees);

        $this->getAccessibleUserTree()->shouldReturn($thirdTree);
    }

    function it_throws_an_exception_if_default_tree_is_not_accessible(
        $user,
        $authorizationChecker,
        $categoryRepository,
        $categoryAccessRepo,
        CategoryInterface $firstTree
    ) {
        $user->getDefaultTree()->willReturn($firstTree);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $firstTree)->willReturn(false);

        $categoryAccessRepo->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([1]);
        $categoryRepository->getGrantedTrees([1])->willReturn([]);

        $this->shouldThrow(new \LogicException('User should have a default product tree'))->during('getAccessibleUserTree');
    }

    function it_provides_locale_from_the_request_if_it_has_been_set(
        $request,
        $localeRepository,
        $authorizationChecker,
        LocaleInterface $locale,
        SessionInterface $session
    ) {
        $request->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);
        $locale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(true);

        $request->hasSession()->shouldBeCalled();
        $request->getSession()->willReturn($session);
        $locale->getCode()->willReturn('fr_FR');
        $session->set('dataLocale', 'fr_FR');


        $this->getCurrentGrantedLocale()->shouldReturn($locale);
    }

    function it_provides_user_session_locale_if_locale_is_not_present_in_request(
        $request,
        $localeRepository,
        $authorizationChecker,
        LocaleInterface $locale,
        SessionInterface $session
    ) {
        $request->get('dataLocale')-> willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);
        $locale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)->willReturn(true);

        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $locale->getCode()->willReturn('fr_FR');
        $session->set('dataLocale', 'fr_FR')->shouldBeCalled();
        $session->save()->shouldBeCalled();


        $this->getCurrentGrantedLocale()->shouldReturn($locale);
    }

    function it_provides_user_locale_if_locale_is_not_present_in_user_session(
        $request,
        $localeRepository,
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        LocaleInterface $sessionLocale,
        LocaleInterface $userLocale,
        SessionInterface $session,
        UserInterface $user
    ) {
        $request->get('dataLocale')-> willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($sessionLocale);
        $sessionLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $sessionLocale)->willReturn(false);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willreturn($user);
        $user->getCatalogLocale()->willReturn($userLocale);
        $userLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $userLocale)->willReturn(true);

        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $userLocale->getCode()->willReturn('fr_FR');
        $session->set('dataLocale', 'fr_FR')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentGrantedLocale()->shouldReturn($userLocale);
    }

    function it_provides_default_locale_if_locale_is_not_present_in_user_locale(
        $request,
        $localeRepository,
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        LocaleInterface $sessionLocale,
        LocaleInterface $userLocale,
        LocaleInterface $defaultLocale,
        SessionInterface $session,
        UserInterface $user
    ) {
        $request->get('dataLocale')-> willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($sessionLocale);
        $sessionLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $sessionLocale)->willReturn(false);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willreturn($user);
        $user->getCatalogLocale()->willReturn($userLocale);
        $userLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $userLocale)->willReturn(false);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($defaultLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $defaultLocale)->willReturn(true);

        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $defaultLocale->getCode()->willReturn('en_US');
        $session->set('dataLocale', 'en_US')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentGrantedLocale()->shouldReturn($defaultLocale);
    }

    function it_provides_first_granted_user_locale_if_no_default_locale(
        $request,
        $localeRepository,
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        LocaleInterface $sessionLocale,
        LocaleInterface $userLocale,
        LocaleInterface $defaultLocale,
        LocaleInterface $activatedLocale1,
        LocaleInterface $activatedLocale2,
        SessionInterface $session,
        UserInterface $user
    ) {
        $request->get('dataLocale')-> willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($sessionLocale);
        $sessionLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $sessionLocale)->willReturn(false);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willreturn($user);
        $user->getCatalogLocale()->willReturn($userLocale);
        $userLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $userLocale)->willReturn(false);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($defaultLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $defaultLocale)->willReturn(false);

        $localeRepository->getActivatedLocales()->willReturn([$activatedLocale1, $activatedLocale2]);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $activatedLocale1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $activatedLocale2)->willReturn(true);

        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $activatedLocale1->getCode()->willReturn('en_US');
        $session->set('dataLocale', 'en_US')->shouldBeCalled();
        $session->save()->shouldBeCalled();

        $this->getCurrentGrantedLocale()->shouldReturn($activatedLocale1);
    }

    function it_throws_an_exception_if_there_are_no_activated_locales(
        $request,
        $localeRepository,
        $authorizationChecker,
        $tokenStorage,
        TokenInterface $token,
        LocaleInterface $sessionLocale,
        LocaleInterface $userLocale,
        LocaleInterface $defaultLocale,
        LocaleInterface $activatedLocale1,
        LocaleInterface $activatedLocale2,
        SessionInterface $session,
        UserInterface $user
    ) {
        $request->get('dataLocale')-> willReturn(null);
        $session->get('dataLocale')->willReturn('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($sessionLocale);
        $sessionLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $sessionLocale)->willReturn(false);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willreturn($user);
        $user->getCatalogLocale()->willReturn($userLocale);
        $userLocale->isActivated()->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $userLocale)->willReturn(false);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($defaultLocale);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $defaultLocale)->willReturn(false);

        $localeRepository->getActivatedLocales()->willReturn([$activatedLocale1, $activatedLocale2]);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $activatedLocale1)->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $activatedLocale2)->willReturn(false);

        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $activatedLocale1->getCode()->willReturn('en_US');

        $this
            ->shouldThrow(new \LogicException('User doesn\'t have access to any activated locales'))
            ->during('getCurrentGrantedLocale');
    }
}
