<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess;

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
        $this->shouldHaveType('PimEnterprise\Component\Security\Updater\AssetCategoryAccessUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_asset_category_access()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "PimEnterprise\Bundle\SecurityBundle\Entity\AssetCategoryAccess", "stdClass" provided.'
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_asset_category_access(
        $groupRepository,
        $categoryRepository,
        AssetCategoryAccess $categoryAccess,
        Group $userGroup,
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

    function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        AssetCategoryAccess $categoryAccess
    ) {
        $groupRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('Group with "foo" code does not exist'))
            ->during('update', [$categoryAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_locale_not_found(
        $categoryRepository,
        AssetCategoryAccess $categoryAccess
    ) {
        $categoryRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(new \InvalidArgumentException('Asset category with "foo" code does not exist'))
            ->during('update', [$categoryAccess, ['category' => 'foo']]);
    }
}
