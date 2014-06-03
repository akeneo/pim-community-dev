<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Context;

use Oro\Bundle\UserBundle\Entity\User;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;

class UserContextSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\UserBundle\Context\UserContext');
    }

    function let(
        SecurityContextInterface $securityContext,
        SecurityFacade $securityFacade,
        LocaleManager $localeManager,
        ChannelManager $channelManager,
        CategoryManager $categoryManager,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $securityContext,
            $securityFacade,
            $localeManager,
            $channelManager,
            $categoryManager,
            'en_US'
        );
    }

    function it_gets_the_default_tree_if_accessible($user, $securityContext, CategoryInterface $secondTree)
    {
        $user->getDefaultTree()->willReturn($secondTree);
        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $secondTree)->willReturn(true);

        $this->getAccessibleUserTree()->shouldReturn($secondTree);
    }

    function it_gets_the_first_accessible_tree_if_the_default_user_tree_is_not_accessible(
        $user, $securityContext, $categoryManager,
        CategoryInterface $firstTree, CategoryInterface $secondTree, CategoryInterface $thirdTree
    ) {
        $user->getDefaultTree()->willReturn($secondTree);
        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $secondTree)->willReturn(false);

        $categoryManager->getAccessibleTrees()->willReturn([$thirdTree, $firstTree]);

        $this->getAccessibleUserTree()->shouldReturn($thirdTree);
    }

    function it_throws_an_exception_if_default_tree_is_accessible(
        $user, $securityContext, $categoryManager, CategoryInterface $firstTree
    ) {
        $user->getDefaultTree()->willReturn($firstTree);
        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $firstTree)->willReturn(false);

        $categoryManager->getAccessibleTrees()->willReturn([]);

        $this->shouldThrow(new \LogicException('User should have a default tree'))->during('getAccessibleUserTree');
    }
}
