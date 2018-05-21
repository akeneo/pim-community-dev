<?php

namespace spec\PimEnterprise\Component\Security\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface;

class AttributeGroupAccessUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $userGroupRepo,
        IdentifiableObjectRepositoryInterface $attributeGroupRepo
    ) {
        $this->beConstructedWith($userGroupRepo, $attributeGroupRepo);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\Security\Updater\AttributeGroupAccessUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_asset_category_access()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface'
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_asset_category_access(
        $userGroupRepo,
        $attributeGroupRepo,
        AttributeGroupAccessInterface $groupAccess,
        GroupInterface $userGroup,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'attribute_group'  => 'other',
            'user_group'      => 'IT Manager',
            'view_attributes' => true,
            'edit_attributes' => false,
        ];

        $groupAccess->setAttributeGroup($attributeGroup)->shouldBeCalled();
        $groupAccess->setUserGroup($userGroup)->shouldBeCalled();
        $groupAccess->setViewAttributes(true)->shouldBeCalled();
        $groupAccess->setEditAttributes(false)->shouldBeCalled();

        $userGroupRepo->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $attributeGroupRepo->findOneByIdentifier('other')->willReturn($attributeGroup);

        $this->update($groupAccess, $values, []);
    }

    function it_updates_a_asset_category_access_with_edit_permission_only(
        $userGroupRepo,
        $attributeGroupRepo,
        AttributeGroupAccessInterface $groupAccess,
        GroupInterface $userGroup,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'attribute_group'  => 'other',
            'user_group'      => 'IT Manager',
            'view_attributes' => false,
            'edit_attributes' => true,
        ];

        $groupAccess->setAttributeGroup($attributeGroup)->shouldBeCalled();
        $groupAccess->setUserGroup($userGroup)->shouldBeCalled();
        $groupAccess->setViewAttributes(false)->shouldBeCalled();
        $groupAccess->setViewAttributes(true)->shouldBeCalled();
        $groupAccess->setEditAttributes(true)->shouldBeCalled();

        $userGroupRepo->findOneByIdentifier('IT Manager')->willReturn($userGroup);
        $attributeGroupRepo->findOneByIdentifier('other')->willReturn($attributeGroup);

        $this->update($groupAccess, $values, []);
    }

    function it_throws_an_exception_if_group_not_found(
        $userGroupRepo,
        AttributeGroupAccessInterface $groupAccess
    ) {
        $userGroupRepo->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'user_group',
                'group code',
                'The group does not exist',
                'PimEnterprise\Component\Security\Updater\AttributeGroupAccessUpdater',
                'foo'
            )
        )->during('update', [$groupAccess, ['user_group' => 'foo']]);
    }

    function it_throws_an_exception_if_attribute_group_not_found(
        $attributeGroupRepo,
        AttributeGroupAccessInterface $groupAccess
    ) {
        $attributeGroupRepo->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'attribute_group',
                'attribute group code',
                'The attribute group does not exist',
                'PimEnterprise\Component\Security\Updater\AttributeGroupAccessUpdater',
                'foo'
            )
        )->during('update', [$groupAccess, ['attribute_group' => 'foo']]);
    }
}
