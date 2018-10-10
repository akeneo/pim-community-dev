<?php

namespace Specification\Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Permission\Component\Updater\AssetCategoryAccessUpdater;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\Pim\Permission\Bundle\Entity\AssetCategoryAccess;

class AssetCategoryAccessUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $categoryRepository
    ) {
        $this->beConstructedWith($groupRepository, $categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCategoryAccessUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_asset_category_access()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                \stdClass::class,
                AssetCategoryAccess::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_an_asset_category_access(
        $groupRepository,
        $categoryRepository,
        AssetCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => 'videos',
            'user_group'  => 'IT Manager',
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
        $categoryRepository->findOneByIdentifier('videos')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }

    function it_updates_an_asset_category_access_with_edit_permission_only(
        $groupRepository,
        $categoryRepository,
        AssetCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => 'videos',
            'user_group'  => 'IT Manager',
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
        $categoryRepository->findOneByIdentifier('videos')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }

    function it_updates_an_asset_category_access_with_own_permission_only(
        $groupRepository,
        $categoryRepository,
        AssetCategoryAccess $categoryAccess,
        GroupInterface $userGroup,
        CategoryInterface $category
    ) {
        $values = [
            'category'   => 'videos',
            'user_group'  => 'IT Manager',
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
        $categoryRepository->findOneByIdentifier('videos')->willReturn($category);

        $this->update($categoryAccess, $values, []);
    }

    function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        AssetCategoryAccess $categoryAccess
    ) {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'user_group',
                'group code',
                'The group does not exist',
                AssetCategoryAccessUpdater::class,
                'foo'
            )
        )->during('update', [$categoryAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $categoryRepository,
        AssetCategoryAccess $categoryAccess
    ) {
        $categoryRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'category',
                'category code',
                'The category does not exist',
                AssetCategoryAccessUpdater::class,
                'foo'
            )
        )->during('update', [$categoryAccess, ['category' => 'foo']]);
    }
}
