<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAllAttributeGroupCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupReferenceFromCode;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupsAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use PhpSpec\ObjectBehavior;

class UserGroupAttributeGroupPermissionsSaverSpec extends ObjectBehavior
{
    public function let(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        GroupRepositoryInterface $groupRepository,
        SaverInterface $groupSaver,
        GetAllAttributeGroupCodes $getAllAttributeGroupCodes,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel,
        GetAttributeGroupReferenceFromCode $getAttributeGroupReferenceFromCode,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        AttributeGroupInterface $attributeGroupB,
        AttributeGroupInterface $attributeGroupC
    ) {
        $group->getId()->willReturn(42);

        $attributeGroupA->getId()->willReturn(1);
        $attributeGroupA->getCode()->willReturn('a');
        $attributeGroupB->getId()->willReturn(2);
        $attributeGroupB->getCode()->willReturn('b');
        $attributeGroupC->getId()->willReturn(3);
        $attributeGroupC->getCode()->willReturn('c');

        $getAttributeGroupReferenceFromCode->execute('a')->willReturn($attributeGroupA);
        $getAttributeGroupReferenceFromCode->execute('b')->willReturn($attributeGroupB);
        $getAttributeGroupReferenceFromCode->execute('c')->willReturn($attributeGroupC);

        $getAllAttributeGroupCodes->execute()->willReturn([
            'a',
            'b',
            'c',
        ]);

        $groupRepository->findOneByIdentifier('Redactor')->willReturn($group);

        $getAttributeGroupsAccessesWithHighestLevel->execute(42)->willReturn([]);

        $this->beConstructedWith(
            $attributeGroupAccessManager,
            $groupRepository,
            $groupSaver,
            $getAllAttributeGroupCodes,
            $getAttributeGroupsAccessesWithHighestLevel,
            $getAttributeGroupReferenceFromCode,
        );
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenTheAllByDefaultOptionIsEnabled(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        AttributeGroupInterface $attributeGroupB,
        AttributeGroupInterface $attributeGroupC
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $group->setDefaultPermission('attribute_group_edit', true)->shouldBeCalled();
        $group->setDefaultPermission('attribute_group_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupB, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupC, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_CorrectsGrantedPermissionsOnExistingCategoriesWhenTheDefaultOptionIsReducedToViewOnly(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        AttributeGroupInterface $attributeGroupB,
        AttributeGroupInterface $attributeGroupC
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $group->setDefaultPermission('attribute_group_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('attribute_group_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupB, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupC, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a"]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenIdentifiersAreSelected(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["a"]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenIdentifiersAndTheDefaultOptionAreMixed(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        AttributeGroupInterface $attributeGroupB,
        AttributeGroupInterface $attributeGroupC
    ) {
        $group->getDefaultPermissions()->willReturn(null);
        $group->setDefaultPermission('attribute_group_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('attribute_group_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupB, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupC, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a", "b"]}}
     */
    public function it_GrantPermissionsOnCategoriesWhenIdentifiersAreOnDifferentLevels(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        AttributeGroupInterface $attributeGroupB
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::EDIT_ATTRIBUTES)->shouldBeCalled();
        $attributeGroupAccessManager->grantAccess($attributeGroupB, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'a',
                    'b',
                ],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a"]}}
     * TO {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a"]}}
     */
    public function it_doesNothingWhenIdentifiersWereAlreadySelected(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getAttributeGroupsAccessesWithHighestLevel->execute(42)->willReturn([
            'a' => Attributes::EDIT_ATTRIBUTES,
        ]);

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::EDIT_ATTRIBUTES)->shouldNotBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a"]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":[]}}
     */
    public function it_removeAccessWhenIdentifiersAreRemoved(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getAttributeGroupsAccessesWithHighestLevel->execute(42)->willReturn([
            'a' => Attributes::EDIT_ATTRIBUTES,
        ]);

        $attributeGroupAccessManager->revokeGroupAccess($attributeGroupA, $group)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":false,"identifiers":["a"]},"view":{"all":false,"identifiers":["a"]}}
     * TO {"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":["a"]}}
     */
    public function it_updatesPermissionsWhenIdentifiersAreRemoved(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        AttributeGroupInterface $attributeGroupA,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getAttributeGroupsAccessesWithHighestLevel->execute(42)->willReturn([
            'a' => Attributes::EDIT_ATTRIBUTES,
        ]);

        $attributeGroupAccessManager->grantAccess($attributeGroupA, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'a',
                ],
            ],
        ]);
    }

    /**
     * FROM {"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     * TO {"edit":{"all":false,"identifiers":["a", "b"]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_updatesPermissionsWhenSwitchingFromAllByDefaultToSpecificIdentifiers(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel,
        AttributeGroupInterface $attributeGroupC
    ) {
        $group->getDefaultPermissions()->willReturn([
            'attribute_group_edit' => true,
            'attribute_group_view' => true,
        ]);
        $group->setDefaultPermission('attribute_group_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('attribute_group_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $getAttributeGroupsAccessesWithHighestLevel->execute(42)->willReturn([
            'a' => Attributes::EDIT_ATTRIBUTES,
            'b' => Attributes::EDIT_ATTRIBUTES,
            'c' => Attributes::EDIT_ATTRIBUTES,
        ]);

        $attributeGroupAccessManager->grantAccess($attributeGroupC, $group, Attributes::VIEW_ATTRIBUTES)->shouldBeCalled();

        $this->save('Redactor', [
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'a',
                    'b',
                ],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }
}
