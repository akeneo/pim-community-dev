<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetAllRootCategoriesCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetCategoriesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoryReferenceFromCode;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class UserGroupCategoryPermissionsSaver
{
    private const DEFAULT_PERMISSION_OWN = 'category_own';
    private const DEFAULT_PERMISSION_EDIT = 'category_edit';
    private const DEFAULT_PERMISSION_VIEW = 'category_view';

    private CategoryAccessManager $categoryAccessManager;
    private GroupRepository $groupRepository;
    private SaverInterface $groupSaver;
    private GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode;
    private GetAllRootCategoriesCodes $getAllRootCategoriesCodes;
    private GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel;

    public function __construct(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode,
        GetAllRootCategoriesCodes $getAllRootCategoriesCodes,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel
    ) {
        $this->categoryAccessManager = $categoryAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->getRootCategoryReferenceFromCode = $getRootCategoryReferenceFromCode;
        $this->getAllRootCategoriesCodes = $getAllRootCategoriesCodes;
        $this->getCategoriesAccessesWithHighestLevel = $getCategoriesAccessesWithHighestLevel;
    }

    /**
     * @param string $groupName
     * @param array{
     *      own: array{
     *          all: bool,
     *          identifiers: string[],
     *      },
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
    public function save(string $groupName, array $permissions): void
    {
        $group = $this->getGroup($groupName);
        $this->updateDefaultPermissions($group, $permissions);

        $affectedCategoriesCodes = $this->getAffectedCategoriesCodes($permissions);
        $highestAccessLevelIndexedByCategoryCode = $this->getHighestAccessLevelIndexedByCategoryCode($affectedCategoriesCodes, $permissions);
        $existingHighestAccessLevelIndexedByCategoryCode = $this->getCategoriesAccessesWithHighestLevel->execute($group->getId());
        $removedCategoryCodes = array_diff(array_keys($existingHighestAccessLevelIndexedByCategoryCode), $affectedCategoriesCodes);

        $this->revokeAccesses($removedCategoryCodes, $group);

        $this->updateAccesses($highestAccessLevelIndexedByCategoryCode, $existingHighestAccessLevelIndexedByCategoryCode, $group);
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
     * @param $group
     * @param array $permissions
     */
    private function updateDefaultPermissions(GroupInterface $group, array $permissions): void
    {
        $defaultPermissions = $group->getDefaultPermissions();

        $currentHighestAll = $this->getCurrentHighestAll($defaultPermissions);
        $submittedHighestAll = $this->getSubmittedHighestAll($permissions);

        if ($currentHighestAll === $submittedHighestAll) {
            return;
        }

        $group->setDefaultPermission(self::DEFAULT_PERMISSION_VIEW, in_array($submittedHighestAll, [Attributes::OWN_PRODUCTS, Attributes::EDIT_ITEMS, Attributes::VIEW_ITEMS]));
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_EDIT, in_array($submittedHighestAll, [Attributes::OWN_PRODUCTS, Attributes::EDIT_ITEMS]));
        $group->setDefaultPermission(self::DEFAULT_PERMISSION_OWN, $submittedHighestAll === Attributes::OWN_PRODUCTS);

        $this->groupSaver->save($group);
    }

    private function getCurrentHighestAll($defaultPermission): ?string
    {
        if (true === ($defaultPermission[self::DEFAULT_PERMISSION_OWN] ?? null)) {
            return Attributes::OWN_PRODUCTS;
        } else if (true === ($defaultPermission[self::DEFAULT_PERMISSION_EDIT] ?? null)) {
            return Attributes::EDIT_ITEMS;
        } else if (true === ($defaultPermission[self::DEFAULT_PERMISSION_VIEW] ?? null)) {
            return Attributes::VIEW_ITEMS;
        }

        return null;
    }

    private function getSubmittedHighestAll($permissions): ?string
    {
        if (true === $permissions['own']['all']) {
            return Attributes::OWN_PRODUCTS;
        } else if (true === $permissions['edit']['all']) {
            return Attributes::EDIT_ITEMS;
        } else if (true === $permissions['view']['all']) {
            return Attributes::VIEW_ITEMS;
        }

        return null;
    }

    /**
     * @param array $permissions
     * @return array
     */
    public function getAffectedCategoriesCodes(array $permissions): array
    {
        if ($permissions['own']['all'] || $permissions['edit']['all'] || $permissions['view']['all']) {
            return $this->getAllRootCategoriesCodes->execute();
        }

        return array_values(array_unique(array_merge(
            $permissions['own']['identifiers'],
            $permissions['edit']['identifiers'],
            $permissions['view']['identifiers'],
        )));
    }

    /**
     * @param array $categoriesCodesForAnyAccessLevel
     * @param array $permissions
     * @return array
     */
    private function getHighestAccessLevelIndexedByCategoryCode(array $categoriesCodesForAnyAccessLevel, array $permissions): array
    {
        $highestAccessLevelIndexedByCategoryCode = [];

        foreach ($categoriesCodesForAnyAccessLevel as $code) {
            $highestAccessLevelIndexedByCategoryCode[$code] = $this->getHighestAccessLevelFromPermissions($code, $permissions);
        }

        return $highestAccessLevelIndexedByCategoryCode;
    }

    private function getHighestAccessLevelFromPermissions(string $categoryCode, array $permissions): string
    {
        if (true === $permissions['own']['all'] || in_array($categoryCode, $permissions['own']['identifiers'])) {
            return Attributes::OWN_PRODUCTS;
        } else if (true === $permissions['edit']['all'] || in_array($categoryCode, $permissions['edit']['identifiers'])) {
            return Attributes::EDIT_ITEMS;
        } else if (true === $permissions['view']['all'] || in_array($categoryCode, $permissions['view']['identifiers'])) {
            return Attributes::VIEW_ITEMS;
        }

        throw new \LogicException('Category code is not covered by submitted permissions');
    }

    /**
     * @param string[] $removedCategoryCodes
     * @param GroupInterface $group
     */
    private function revokeAccesses(array $removedCategoryCodes, GroupInterface $group): void
    {
        foreach ($removedCategoryCodes as $categoryCode) {
            $category = $this->getRootCategoryReferenceFromCode->execute($categoryCode);
            if (null === $category) {
                continue;
            }
            $this->categoryAccessManager->revokeGroupAccess($category, $group);
        }
    }

    /**
     * @param array<string, string> $highestAccessLevelIndexedByCategoryCode
     * @param array<string, string> $existingHighestAccessLevelIndexedByCategoryCode
     * @param $group
     */
    private function updateAccesses(array $highestAccessLevelIndexedByCategoryCode, array $existingHighestAccessLevelIndexedByCategoryCode, GroupInterface $group): void
    {
        foreach ($highestAccessLevelIndexedByCategoryCode as $categoryCode => $newLevel) {
            $existingLevel = $existingHighestAccessLevelIndexedByCategoryCode[$categoryCode] ?? null;

            if ($existingLevel !== $newLevel) {
                $category = $this->getRootCategoryReferenceFromCode->execute($categoryCode);
                if (null === $category) {
                    continue;
                }
                $this->categoryAccessManager->grantAccess($category, $group, $newLevel);
            }
        }
    }
}
