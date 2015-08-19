<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Prophecy\Argument;

class CategoryAccessManagerSpec extends ObjectBehavior
{
    function let(
        SmartManagerRegistry $registry,
        ObjectManager $objectManager,
        CategoryAccessRepository $accessRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);

        $accessClass = 'PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess';
        $categoryClass = 'Pim\Bundle\CatalogBundle\Entity\CategoryInterface';
        $userGroupClass = 'Pim\Bundle\SecurityBundle\Entity\Group';
        $registry->getRepository($accessClass)->willReturn($accessRepository);
        $registry->getRepository($categoryClass)->willReturn($categoryRepository);

        $this->beConstructedWith($registry, $accessClass, $categoryClass, $userGroupClass);
    }

    function it_provides_user_groups_that_have_access_to_a_category(CategoryInterface $category, $accessRepository)
    {
        $accessRepository->getGrantedUserGroups($category, Attributes::VIEW_PRODUCTS)->willReturn(['foo', 'bar']);
        $accessRepository->getGrantedUserGroups($category, Attributes::EDIT_PRODUCTS)->willReturn(['bar']);
        $accessRepository->getGrantedUserGroups($category, Attributes::OWN_PRODUCTS)->willReturn(['bar']);

        $this->getViewUserGroups($category)->shouldReturn(['foo', 'bar']);
        $this->getEditUserGroups($category)->shouldReturn(['bar']);
        $this->getOwnUserGroups($category)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_category_for_the_provided_user_groups(
        CategoryInterface $category,
        $accessRepository,
        $objectManager,
        Group $user,
        Group $admin
    ) {
        $category->getId()->willReturn(1);
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessRepository->revokeAccess($category, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin], [$admin], true);
    }

    function it_grants_access_on_a_category_for_the_provided_user_groups_and_does_not_flush(
        CategoryInterface $category,
        $accessRepository,
        $objectManager,
        Group $user,
        Group $admin
    ) {
        $category->getId()->willReturn(1);
        $accessRepository->findOneBy(Argument::any())->willReturn(array());
        $accessRepository->revokeAccess($category, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess'))
            ->shouldBeCalledTimes(2);

        $this->setAccess($category, [$user, $admin], [$admin], [$admin], false);
    }

    function it_grants_access_on_a_new_category_for_the_provided_user_groups(
        CategoryInterface $category,
        $accessRepository,
        $objectManager,
        Group $user,
        Group $admin
    ) {
        $accessRepository->findOneBy(Argument::any())->willReturn(array());

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin], [$admin], true);
    }

    function it_adds_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        CategoryInterface $childOne,
        CategoryInterface $childTwo,
        $categoryRepository,
        $accessRepository,
        Group $user,
        $objectManager
    ) {
        $addViewGroups = [$user];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [];
        $removeEditGroups = [];
        $removeOwnGroups = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn([]);

        $categoryRepository->findBy(['id' => $childrenIds])->willReturn([$childOne, $childTwo]);
        $objectManager->persist(Argument::any())->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }

    function it_updates_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        ProductCategoryAccess $accessOne,
        ProductCategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Group $user,
        $objectManager
    ) {
        $addViewGroups = [$user];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [];
        $removeEditGroups = [];
        $removeOwnGroups = [];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$user], $childrenIds)->willReturn($childrenIds);

        $accessRepository
            ->findBy(['category' => $childrenIds, 'userGroup' => $user])
            ->willReturn([$accessOne, $accessTwo]);

        $accessOne->setViewItems(true)->shouldBeCalled();
        $accessTwo->setViewItems(true)->shouldBeCalled();
        $objectManager->persist($accessOne)->shouldBeCalled();
        $objectManager->persist($accessTwo)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }

    function it_removes_accesses_on_a_category_children_for_the_provided_user_groups(
        CategoryInterface $parent,
        ProductCategoryAccess $accessOne,
        ProductCategoryAccess $accessTwo,
        $categoryRepository,
        $accessRepository,
        Group $manager,
        $objectManager
    ) {
        $addViewGroups = [];
        $addEditGroups = [];
        $addOwnGroups = [];
        $removeViewGroups = [$manager];
        $removeEditGroups = [$manager];
        $removeOwnGroups = [$manager];

        $childrenIds = [42, 19];
        $categoryRepository->getAllChildrenIds($parent)->willReturn($childrenIds);
        $accessRepository->getCategoryIdsWithExistingAccess([$manager], $childrenIds)->willReturn($childrenIds);

        $accessRepository
            ->findBy(['category' => $childrenIds, 'userGroup' => $manager])
            ->willReturn([$accessOne, $accessTwo]);

        $objectManager->remove($accessOne)->shouldBeCalled();
        $objectManager->remove($accessTwo)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->updateChildrenAccesses(
            $parent,
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
    }
}
