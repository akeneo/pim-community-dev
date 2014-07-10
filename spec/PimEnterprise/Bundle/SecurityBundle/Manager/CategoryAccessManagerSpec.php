<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Category;
use PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

class CategoryAccessManagerSpec extends ObjectBehavior
{
    function let(SmartManagerRegistry $registry, ObjectManager $objectManager, CategoryAccessRepository $accessRepository, CategoryRepository $categoryRepository)
    {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);

        $accessClass = 'PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess';
        $categoryClass = 'Pim\Bundle\CatalogBundle\Entity\Category';
        $registry->getRepository($accessClass)->willReturn($accessRepository);
        $registry->getRepository($categoryClass)->willReturn($categoryRepository);

        $this->beConstructedWith($registry, $accessClass, $categoryClass);
    }

    function it_provides_roles_that_have_access_to_a_category(Category $category, $accessRepository)
    {
        $accessRepository->getGrantedRoles($category, CategoryVoter::VIEW_PRODUCTS)->willReturn(['foo', 'bar']);
        $accessRepository->getGrantedRoles($category, CategoryVoter::EDIT_PRODUCTS)->willReturn(['bar']);

        $this->getViewRoles($category)->shouldReturn(['foo', 'bar']);
        $this->getEditRoles($category)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_category_for_the_provided_roles(
        Category $category,
        $accessRepository,
        $objectManager,
        Role $user,
        Role $admin
    ) {
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessRepository->revokeAccess($category, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin]);
    }

    function it_adds_accesses_on_a_category_children_for_the_provided_roles(
        Category $parent,
        Category $childOne,
        Category $childTwo,
        $categoryRepository,
        $accessRepository,
        Role $user,
        $objectManager
    ) { 
        $addViewRoles = [$user];
        $addEditRoles = [];
        $removeViewRoles = [];
        $removeEditRoles = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn([]);

        $categoryRepository->findBy(['id' => $childrenIds])->willReturn([$childOne, $childTwo]);
        $objectManager->persist(Argument::any())->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses($parent, $addViewRoles, $addEditRoles, $removeViewRoles, $removeEditRoles);
    }

    function it_updates_accesses_on_a_category_children_for_the_provided_roles(
        Category $parent,
        CategoryAccess $accessOne,
        CategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Role $user,
        $objectManager
    ) {
        $addViewRoles = [$user];
        $addEditRoles = [];
        $removeViewRoles = [];
        $removeEditRoles = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn($childrenIds);

        $accessRepository->findBy(['category' => $childrenIds, 'role' => $user])->willReturn([$accessOne, $accessTwo]);
        $accessOne->setViewProducts(true)->shouldBeCalled();
        $accessTwo->setViewProducts(true)->shouldBeCalled();
        $objectManager->persist($accessOne)->shouldBeCalled();
        $objectManager->persist($accessTwo)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses($parent, $addViewRoles, $addEditRoles, $removeViewRoles, $removeEditRoles);
    }

    function it_removes_accesses_on_a_category_children_for_the_provided_roles(
        Category $parent,
        CategoryAccess $accessOne,
        CategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Role $user,
        $objectManager
    ) {
        $addViewRoles = [];
        $addEditRoles = [];
        $removeViewRoles = [$user];
        $removeEditRoles = [$user];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn($childrenIds);

        $accessRepository->findBy(['category' => $childrenIds, 'role' => $user])->willReturn([$accessOne, $accessTwo]);
        $objectManager->remove($accessOne)->shouldBeCalled();
        $objectManager->remove($accessTwo)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses($parent, $addViewRoles, $addEditRoles, $removeViewRoles, $removeEditRoles);
    }
}
