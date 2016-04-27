<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CategoryManagerSpec extends ObjectBehavior
{
    function let(
        CategoryRepositoryInterface $categoryRepository,
        CategoryAccessRepository $categoryAccessRepo,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $categoryAccessRepo,
            $authorizationChecker
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager');
    }

    function it_gets_accessible_trees_for_display(
        $categoryAccessRepo,
        $categoryRepository,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree,
        UserInterface $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository->getTrees()->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = [1, 3];

        $categoryAccessRepo
            ->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user)->shouldReturn([$firstTree, $thirdTree]);
    }

    function it_gets_accessible_trees_for_edition(
        $categoryAccessRepo,
        $categoryRepository,
        CategoryInterface $firstTree,
        CategoryInterface $secondTree,
        CategoryInterface $thirdTree,
        UserInterface $user
    ) {
        $firstTree->getId()->willReturn(1);
        $secondTree->getId()->willReturn(2);
        $thirdTree->getId()->willReturn(3);

        $categoryRepository->getTrees()->willReturn([$firstTree, $secondTree, $thirdTree]);

        $accessibleCategoryIds = [1];

        $categoryAccessRepo
            ->getGrantedCategoryIds($user, Attributes::EDIT_ITEMS)
            ->willReturn($accessibleCategoryIds);

        $this->getAccessibleTrees($user, Attributes::EDIT_ITEMS)->shouldReturn([$firstTree]);
    }

    function it_gets_granted_filled_tree_when_path_is_not_granted(
        $categoryRepository,
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo
    ) {
        $categoryRepository->getFilledTree($parent, new ArrayCollection([$childTwo]))->willReturn(
            [0 => $parent, 1 => $childOne, 2 => $childTwo]
        );
        $parent->getId()->willReturn(3);
        $childOne->getId()->willReturn(1);
        $childTwo->getId()->willReturn(2);
        $this->getGrantedFilledTree($parent, new ArrayCollection([$childTwo]));
    }

    function it_gets_granted_filled_tree_when_path_is_granted(
        $categoryRepository,
        $authorizationChecker,
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo
    ) {
        $categoryRepository->getFilledTree($parent, new ArrayCollection([$childTwo]))->willReturn(
            [0 => $parent, 1 => $childOne, 2 => $childTwo]
        );
        $parent->getId()->willReturn(3);
        $childOne->getId()->willReturn(1);
        $childTwo->getId()->willReturn(2);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $parent)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $childOne)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $childTwo)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $parent)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $childOne)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $childTwo)->willReturn(true);

        $this->getGrantedFilledTree($parent, new ArrayCollection([$childTwo]));
    }
}
