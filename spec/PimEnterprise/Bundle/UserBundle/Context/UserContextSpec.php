<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Context;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ChainedFilter;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
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
        AuthorizationCheckerInterface $authorizationChecker,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        CategoryRepositoryInterface $productCategoryRepo,
        CategoryRepositoryInterface $assetCategoryRepo,
        ChainedFilter $chainedFilter,
        TokenInterface $token,
        User $user,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $tokenStorage,
            $localeManager,
            $channelManager,
            $productCategoryRepo,
            $assetCategoryRepo,
            $chainedFilter,
            $requestStack,
            $authorizationChecker,
            'en_US'
        );
    }

    function it_gets_the_default_tree_if_accessible($user, $authorizationChecker, CategoryInterface $secondTree)
    {
        $user->getDefaultTree()->willReturn($secondTree);
        $authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $secondTree)->willReturn(true);

        $this->getAccessibleUserTree()->shouldReturn($secondTree);
    }

    function it_gets_the_first_accessible_tree_if_the_default_user_tree_is_not_accessible(
        $user,
        $authorizationChecker,
        $productCategoryRepo,
        $chainedFilter,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree
    ) {
        $user->getDefaultTree()->willReturn($secondTree);
        $authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $secondTree)->willReturn(false);

        $grantedTrees = [$thirdTree, $firstTree];
        $productCategoryRepo->getTrees()->willReturn($grantedTrees);
        $chainedFilter->filterCollection($grantedTrees, 'pim.internal_api.product_category.view')
            ->willReturn($grantedTrees);

        $this->getAccessibleUserTree()->shouldReturn($thirdTree);
    }

    function it_throws_an_exception_if_default_tree_is_accessible(
        $user,
        $authorizationChecker,
        $productCategoryRepo,
        $chainedFilter,
        CategoryInterface $firstTree
    ) {
        $user->getDefaultTree()->willReturn($firstTree);
        $authorizationChecker->isGranted(Attributes::VIEW_PRODUCTS, $firstTree)->willReturn(false);

        $productCategoryRepo->getTrees()->willReturn([]);
        $chainedFilter->filterCollection([], 'pim.internal_api.product_category.view')
            ->willReturn([]);

        $this->shouldThrow(new \LogicException('User should have a default product tree'))->during('getAccessibleUserTree');
    }
}
