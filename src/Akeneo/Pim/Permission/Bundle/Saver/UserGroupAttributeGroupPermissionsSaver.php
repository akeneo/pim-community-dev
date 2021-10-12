<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAllAttributeGroupCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupReferenceFromCode;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\AttributeGroup\GetAttributeGroupsAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;

class UserGroupAttributeGroupPermissionsSaver
{
    private const DEFAULT_PERMISSION_EDIT = 'attribute_group_edit';
    private const DEFAULT_PERMISSION_VIEW = 'attribute_group_view';

    private const PERMISSION_EDIT = Attributes::EDIT_ATTRIBUTES;
    private const PERMISSION_VIEW = Attributes::VIEW_ATTRIBUTES;

    private AttributeGroupAccessManager $attributeGroupAccessManager;
    private GroupRepositoryInterface $groupRepository;
    private SaverInterface $groupSaver;
    private GetAllAttributeGroupCodes $getAllAttributeGroupCodes;
    private GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel;
    private GetAttributeGroupReferenceFromCode $getAttributeGroupReferenceFromCode;

    public function __construct(
        AttributeGroupAccessManager $attributeGroupAccessManager,
        GroupRepositoryInterface $groupRepository,
        SaverInterface $groupSaver,
        GetAllAttributeGroupCodes $getAllAttributeGroupCodes,
        GetAttributeGroupsAccessesWithHighestLevel $getAttributeGroupsAccessesWithHighestLevel,
        GetAttributeGroupReferenceFromCode $getAttributeGroupReferenceFromCode
    ) {
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->getAllAttributeGroupCodes = $getAllAttributeGroupCodes;
        $this->getAttributeGroupsAccessesWithHighestLevel = $getAttributeGroupsAccessesWithHighestLevel;
        $this->getAttributeGroupReferenceFromCode = $getAttributeGroupReferenceFromCode;
    }

    /**
     * @param string $groupName
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     * @throws \LogicException
     */
    public function save(string $groupName, array $permissions): void
    {
        $group = $this->getGroup($groupName);
        $this->updateDefaultPermissions($group, $permissions);

        $affectedAttributeGroupCodes = $this->getAffectedAttributeGroupCodes($permissions);
        $highestAccessLevelIndexedByAttributeGroupCode = $this->getHighestAccessLevelIndexedByAttributeGroupCode(
            $affectedAttributeGroupCodes,
            $permissions
        );
        $existingHighestAccessLevelIndexedByAttributeGroupCode = $this->getAttributeGroupsAccessesWithHighestLevel
            ->execute($group->getId());
        $removedAttributeGroupCodes = array_diff(
            array_keys($existingHighestAccessLevelIndexedByAttributeGroupCode),
            $affectedAttributeGroupCodes
        );

        $this->revokeAccesses($removedAttributeGroupCodes, $group);

        $this->updateAccesses(
            $highestAccessLevelIndexedByAttributeGroupCode,
            $existingHighestAccessLevelIndexedByAttributeGroupCode,
            $group
        );
    }

    private function getGroup(string $groupName): GroupInterface
    {
        $group = $this->groupRepository->findOneByIdentifier($groupName);

        if (null === $group) {
            throw new \LogicException('User group not found');
        }

        return $group;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function updateDefaultPermissions(GroupInterface $group, array $permissions): void
    {
        $defaultPermissions = $group->getDefaultPermissions();

        $currentHighestAll = $this->getCurrentHighestAll($defaultPermissions);
        $submittedHighestAll = $this->getSubmittedHighestAll($permissions);

        if ($currentHighestAll === $submittedHighestAll) {
            return;
        }

        $group->setDefaultPermission(
            self::DEFAULT_PERMISSION_VIEW,
            in_array($submittedHighestAll, [
                self::PERMISSION_EDIT,
                self::PERMISSION_VIEW,
            ])
        );
        $group->setDefaultPermission(
            self::DEFAULT_PERMISSION_EDIT,
            $submittedHighestAll === self::PERMISSION_EDIT
        );

        $this->groupSaver->save($group);
    }

    /**
     * @param null | array<string, bool> $defaultPermission
     */
    private function getCurrentHighestAll(?array $defaultPermission): ?string
    {
        if (true === ($defaultPermission[self::DEFAULT_PERMISSION_EDIT] ?? null)) {
            return self::PERMISSION_EDIT;
        } elseif (true === ($defaultPermission[self::DEFAULT_PERMISSION_VIEW] ?? null)) {
            return self::PERMISSION_VIEW;
        }

        return null;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function getSubmittedHighestAll(array $permissions): ?string
    {
        if (true === $permissions['edit']['all']) {
            return self::PERMISSION_EDIT;
        } elseif (true === $permissions['view']['all']) {
            return self::PERMISSION_VIEW;
        }

        return null;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    public function getAffectedAttributeGroupCodes(array $permissions): array
    {
        if ($permissions['edit']['all'] || $permissions['view']['all']) {
            return $this->getAllAttributeGroupCodes->execute();
        }

        return array_values(
            array_unique(
                array_merge(
                    $permissions['edit']['identifiers'],
                    $permissions['view']['identifiers'],
                )
            )
        );
    }

    /**
     * @param string[] $attributeGroupsCodesForAnyAccessLevel
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     * @return array<string, string>
     */
    private function getHighestAccessLevelIndexedByAttributeGroupCode(
        array $attributeGroupsCodesForAnyAccessLevel,
        array $permissions
    ): array {
        $highestAccessLevelIndexedByAttributeGroupCode = [];

        foreach ($attributeGroupsCodesForAnyAccessLevel as $code) {
            $highestAccessLevelIndexedByAttributeGroupCode[$code] = $this->getHighestAccessLevelFromPermissions(
                $code,
                $permissions
            );
        }

        return $highestAccessLevelIndexedByAttributeGroupCode;
    }

    /**
     * @param array{
     *      edit: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
     *      view: array{
     *          all: bool,
     *          identifiers: string[],
     *      }
     * } $permissions
     */
    private function getHighestAccessLevelFromPermissions(string $attributeGroupCode, array $permissions): string
    {
        if (true === $permissions['edit']['all']
            || in_array($attributeGroupCode, $permissions['edit']['identifiers'])) {
            return self::PERMISSION_EDIT;
        } elseif (true === $permissions['view']['all']
            || in_array($attributeGroupCode, $permissions['view']['identifiers'])) {
            return self::PERMISSION_VIEW;
        }

        throw new \LogicException('Attribute group code is not covered by submitted permissions');
    }

    /**
     * @param string[] $removedAttributeGroupCodes
     */
    private function revokeAccesses(array $removedAttributeGroupCodes, GroupInterface $group): void
    {
        foreach ($removedAttributeGroupCodes as $code) {
            $attributeGroup = $this->getAttributeGroupReferenceFromCode->execute($code);
            if (null === $attributeGroup) {
                continue;
            }
            $this->attributeGroupAccessManager->revokeGroupAccess($attributeGroup, $group);
        }
    }

    /**
     * @param array<string, string> $highestAccessLevelIndexedByCode
     * @param array<string, string> $existingHighestAccessLevelIndexedByCode
     */
    private function updateAccesses(
        array $highestAccessLevelIndexedByCode,
        array $existingHighestAccessLevelIndexedByCode,
        GroupInterface $group
    ): void {
        foreach ($highestAccessLevelIndexedByCode as $code => $newLevel) {
            $existingLevel = $existingHighestAccessLevelIndexedByCode[$code] ?? null;

            if ($existingLevel !== $newLevel) {
                $attributeGroup = $this->getAttributeGroupReferenceFromCode->execute($code);
                if (null === $attributeGroup) {
                    continue;
                }
                $this->attributeGroupAccessManager->grantAccess($attributeGroup, $group, $newLevel);
            }
        }
    }
}
