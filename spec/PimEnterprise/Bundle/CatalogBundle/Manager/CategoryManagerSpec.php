<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CategoryManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager');
    }

    function let(
        CategoryAccessRepository $categoryAccessRepository,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        CategoryRepositoryInterface $categoryRepository,
        SecurityContextInterface $context
    ) {
        $om->getRepository(Argument::any())->willReturn($categoryRepository);
        $this->beConstructedWith(
            $om,
            Argument::any(),
            $eventDispatcher,
            $categoryAccessRepository,
            $context
        );
    }

    function it_gets_accessible_trees_for_display(
        $categoryAccessRepository,
        $categoryRepository,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree,
        User $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository
            ->getChildren(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = array(1, 3);

        $categoryAccessRepository
            ->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user)->shouldReturn([$firstTree, $thirdTree]);
    }

    function it_gets_accessible_trees_for_edition(
        $categoryAccessRepository,
        $categoryRepository,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree,
        User $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository
            ->getChildren(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = array(1);

        $categoryAccessRepository
            ->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user, Attributes::EDIT_PRODUCTS)->shouldReturn([$firstTree]);
    }

    function it_gets_granted_children(
        $categoryRepository,
        CategoryInterface $childOne,
        CategoryInterface $childTwo,
        $context
    ) {
        $categoryRepository->getChildrenByParentId(42)->willReturn([$childOne, $childTwo]);
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childOne)->shouldBeCalled();
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childTwo)->shouldBeCalled();
        $this->getGrantedChildren(42);
    }

    function it_gets_granted_filled_tree_when_path_is_not_granted(
        $categoryRepository,
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo,
        $context
    ) {

        $categoryRepository->getPath($childTwo)->willReturn(
            [0 => $parent, 1 => $childOne, 2 => $childTwo]
        );
        $parent->getId()->willReturn(3);
        $childOne->getId()->willReturn(1);
        $childTwo->getId()->willReturn(2);
        $categoryRepository->getTreeFromParents([3, 1, 2])->willReturn([]);
        $this->getGrantedFilledTree($parent, new ArrayCollection([$childTwo]));
    }

    function it_gets_granted_filled_tree_when_path_is_granted(
        $categoryRepository,
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo,
        $context
    ) {
        $categoryRepository->getPath($childTwo)->willReturn(
            [0 => $parent, 1 => $childOne, 2 => $childTwo]
        );
        $parent->getId()->willReturn(3);
        $childOne->getId()->willReturn(1);
        $childTwo->getId()->willReturn(2);

        $context->isGranted(Attributes::VIEW_PRODUCTS, $parent)->willReturn(true);
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childOne)->willReturn(true);
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childTwo)->willReturn(true);
        $categoryRepository->getTreeFromParents([3, 1, 2])->willReturn(
            [
                0 => [
                    'item' => $parent,
                    '__children' => [
                        0 => [
                            'item' => $childOne,
                            '__children' => [
                                0 => $childTwo
                            ]
                        ]
                    ]
                ]
            ]
        );
        $context->isGranted(Attributes::VIEW_PRODUCTS, $parent)->willReturn(true);
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childOne)->willReturn(true);
        $context->isGranted(Attributes::VIEW_PRODUCTS, $childTwo)->willReturn(true);

        $this->getGrantedFilledTree($parent, new ArrayCollection([$childTwo]));
    }
}
