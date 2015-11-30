<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Context;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ChoicesBuilderInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\HttpFoundation\RequestStack;
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
        UserInterface $user
    ) {
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
}
