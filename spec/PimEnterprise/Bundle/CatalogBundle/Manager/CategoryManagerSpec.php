<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CategoryManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager');
    }

    function let(
        SecurityContextInterface $securityContext,
        ObjectManager $om,
        CategoryRepository $categoryRepository,
        TokenInterface $token,
        User $user
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $om->getRepository(Argument::any())->willReturn($categoryRepository);
        $this->beConstructedWith($securityContext, $om, Argument::any());
    }

    function it_gets_accessible_trees(
        $securityContext,
        $categoryRepository,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree
    ) {
        $categoryRepository
            ->getChildren(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn([$firstTree, $secondTree, $thirdTree]);

        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $firstTree)->willReturn(true);
        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $secondTree)->willReturn(false);
        $securityContext->isGranted(CategoryVoter::VIEW_PRODUCTS, $thirdTree)->willReturn(true);

        $this->getAccessibleTrees()->shouldReturn([$firstTree, $thirdTree]);
    }
}
