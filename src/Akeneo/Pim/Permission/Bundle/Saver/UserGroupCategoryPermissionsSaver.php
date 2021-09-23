<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetAllRootCategoriesCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetCategoriesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoriesReferences;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoriesReferencesFromCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoryReferenceFromCode;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
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
    private GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes;
    private GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode;
    private GetRootCategoriesReferences $getRootCategoryReferences;
    private GetAllRootCategoriesCodes $getAllRootCategoriesCodes;
    private GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel;

    public function __construct(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes,
        GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode,
        GetRootCategoriesReferences $getRootCategoryReferences,
        GetAllRootCategoriesCodes $getAllRootCategoriesCodes,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel
    ) {
        $this->categoryAccessManager = $categoryAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->getRootCategoriesReferencesFromCodes = $getRootCategoriesReferencesFromCodes;
        $this->getRootCategoryReferenceFromCode = $getRootCategoryReferenceFromCode;
        $this->getRootCategoryReferences = $getRootCategoryReferences;
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

        $categoriesCodesForAnyAccessLevel = $this->getCategoriesCodesForAnyAccessLevel($permissions);
        $manuallySelectedCategoriesCodes = $this->getManuallySelectedCategoriesCodes($permissions);

        $this->updateDefaultPermissions($group, $permissions);

        $existingCategoriesAccessesByAccessLevel = $this->getCategoriesAccessesWithHighestLevel->execute($group->getId());

        $removedCategoryCodes = array_diff(array_keys($existingCategoriesAccessesByAccessLevel), $categoriesCodesForAnyAccessLevel);

        $this->revokeCategoryAccessOnRemovedCodes($group, $removedCategoryCodes);

        $this->updateCategoryAccessesForManuallySelectedCodes(
            $group,
            $permissions,
            $manuallySelectedCategoriesCodes,
            $existingCategoriesAccessesByAccessLevel,
        );
    }

    private function getGroup(string $groupName)
    {
        $group = $this->groupRepository->findOneByIdentifier($groupName);

        if (null === $group) {
            throw new \LogicException('User group not found');
        }
        return $group;
    }

    private function revokeCategoryAccessOnRemovedCodes(GroupInterface $group, array $categoryCodesToRevoke): void
    {
        if (!empty($categoryCodesToRevoke)) {
            $removedCategories = $this->getRootCategoriesReferencesFromCodes->execute($categoryCodesToRevoke);

            foreach ($removedCategories as $removedCategory) {
                $this->categoryAccessManager->revokeGroupAccess($removedCategory, $group);
            }
        }
    }

    private function updateCategoryAccessesForManuallySelectedCodes(
        GroupInterface $group,
        array $permissions,
        array $manuallySelectedCategoriesCodes,
        array $existingCategoriesAccessesByAccessLevel
    ): void {
        foreach ($manuallySelectedCategoriesCodes as $code) {
            $newAccessLevel = $this->getHighestAccessLevelForSubmittedCode($permissions, $code);
            $currentAccessLevel = $existingCategoriesAccessesByAccessLevel[$code] ?? null;

            if ($currentAccessLevel !== $newAccessLevel) {
                $category = $this->getRootCategoryReferenceFromCode->execute($code);
                $this->categoryAccessManager->grantAccess($category, $group, $newAccessLevel);
            }
        }
    }

    private function getHighestAccessLevelForSubmittedCode(array $permissions, string $categoryCode): string
    {
        if (in_array($categoryCode, $permissions['own']['identifiers'])) {
            return Attributes::OWN_PRODUCTS;
        } elseif (in_array($categoryCode, $permissions['edit']['identifiers'])) {
            return Attributes::EDIT_ITEMS;
        } else {
            return Attributes::VIEW_ITEMS;
        }
    }

    /**
     * @param $group
     * @param array $permissions
     */
    private function updateDefaultPermissions($group, array $permissions): void
    {
        $manuallySelectedCategoriesCodes = $this->getManuallySelectedCategoriesCodes($permissions);

        if (!empty($manuallySelectedCategoriesCodes)) {
            $manuallySelectedCategories = $this->getRootCategoriesReferencesFromCodes->execute($manuallySelectedCategoriesCodes);
            $manuallySelectedCategoriesIds = array_map(fn(CategoryInterface $category) => $category->getId(), $manuallySelectedCategories);
        } else {
            $manuallySelectedCategoriesIds = [];
        }

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

        $categories = $this->getRootCategoryReferences->execute();
        foreach ($categories as $category) {
            if (!empty($manuallySelectedCategoriesIds) && in_array($category->getId(), $manuallySelectedCategoriesIds)) {
                continue;
            }

            $this->categoryAccessManager->grantAccess($category, $group, $submittedHighestAll);
        }
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
    public function getCategoriesCodesForAnyAccessLevel(array $permissions): array
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
     * @param array $permissions
     * @return array
     */
    public function getManuallySelectedCategoriesCodes(array $permissions): array
    {
        return array_values(array_unique(array_merge(
            $permissions['own']['identifiers'],
            $permissions['edit']['identifiers'],
            $permissions['view']['identifiers'],
        )));
    }
}
