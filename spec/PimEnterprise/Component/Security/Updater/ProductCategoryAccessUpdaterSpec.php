<?php

namespace spec\PimEnterprise\Component\Security\Updater;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess;

class ProductCategoryAccessUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($groupRepository, $categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Security\Updater\ProductCategoryAccessUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_a_product_category_access()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Bundle\SecurityBundle\Entity\ProductCategoryAccess'
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_product_category_access(
        $groupRepository,
        $categoryRepository,
        ProductCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => '2013_collection',
            'user_group' => 'IT Manager',
            'view_items' => true,
            'edit_items' => false,
            'own_items'  => false,
        ];

        $categoryAccess->setCategory($category)->shouldBeCalled();
        $categoryAccess->setUserGroup($userGroup)->shouldBeCalled();
        $categoryAccess->setViewItems(true)->shouldBeCalled();
        $categoryAccess->setEditItems(false)->shouldBeCalled();
        $categoryAccess->setOwnItems(false)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $categoryRepository->findOneByIdentifier('2013_collection')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }

    function it_updates_a_product_category_access_with_edit_permission_only(
        $groupRepository,
        $categoryRepository,
        ProductCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => '2013_collection',
            'user_group' => 'IT Manager',
            'view_items' => false,
            'edit_items' => true,
            'own_items'  => false,
        ];

        $categoryAccess->setCategory($category)->shouldBeCalled();
        $categoryAccess->setUserGroup($userGroup)->shouldBeCalled();
        $categoryAccess->setViewItems(false)->shouldBeCalled();
        $categoryAccess->setViewItems(true)->shouldBeCalled();
        $categoryAccess->setEditItems(true)->shouldBeCalled();
        $categoryAccess->setOwnItems(false)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $categoryRepository->findOneByIdentifier('2013_collection')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }


    function it_updates_a_product_category_access_with_own_permission_only(
        $groupRepository,
        $categoryRepository,
        ProductCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => '2013_collection',
            'user_group' => 'IT Manager',
            'view_items' => false,
            'edit_items' => false,
            'own_items'  => true,
        ];

        $categoryAccess->setCategory($category)->shouldBeCalled();
        $categoryAccess->setUserGroup($userGroup)->shouldBeCalled();
        $categoryAccess->setViewItems(false)->shouldBeCalled();
        $categoryAccess->setEditItems(false)->shouldBeCalled();
        $categoryAccess->setViewItems(true)->shouldBeCalled();
        $categoryAccess->setEditItems(true)->shouldBeCalled();
        $categoryAccess->setOwnItems(true)->shouldBeCalled();

        $groupRepository->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $categoryRepository->findOneByIdentifier('2013_collection')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }

    function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        ProductCategoryAccess $categoryAccess
    ) {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'user_group',
                'group code',
                'The group does not exist',
                'PimEnterprise\Component\Security\Updater\ProductCategoryAccessUpdater',
                'foo'
            )
        )->during('update', [$categoryAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $categoryRepository,
        ProductCategoryAccess $categoryAccess
    ) {
        $categoryRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'category',
                'category code',
                'The category does not exist',
                'PimEnterprise\Component\Security\Updater\ProductCategoryAccessUpdater',
                'foo'
            )
        )->during('update', [$categoryAccess, ['category' => 'foo']]);
    }
}
