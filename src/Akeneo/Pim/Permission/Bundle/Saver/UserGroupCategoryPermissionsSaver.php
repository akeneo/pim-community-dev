<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoriesReferencesFromCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoryReferenceFromCode;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class UserGroupCategoryPermissionsSaver
{
    private CategoryAccessManager $categoryAccessManager;
    private GroupRepository $groupRepository;
    private SaverInterface $groupSaver;
    private CategoryRepositoryInterface $categoryRepository;
    private GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes;
    private GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode;

    public function __construct(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        CategoryRepositoryInterface $categoryRepository,
        GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes,
        GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode
    ) {
        $this->categoryAccessManager = $categoryAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->categoryRepository = $categoryRepository;
        $this->getRootCategoriesReferencesFromCodes = $getRootCategoriesReferencesFromCodes;
        $this->getRootCategoryReferenceFromCode = $getRootCategoryReferenceFromCode;
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
        $group = $this->groupRepository->findOneByIdentifier($groupName);

        if (null === $group) {
            throw new \LogicException('User group not found');
        }

        $this->updateDefaultPermissions($group, $permissions);

        $categoriesByAccessLevel = $this->getCategoriesByAccessLevel($group);

        $preservedCategoryCodes = $permissions['view']['identifiers'];
        $removedCategoryCodes = array_diff(array_keys($categoriesByAccessLevel), $preservedCategoryCodes);

        if (!empty($removedCategoryCodes)) {
            $removedCategories = $this->getRootCategoriesReferencesFromCodes->execute($removedCategoryCodes);

            foreach ($removedCategories as $removedCategory) {
                $this->categoryAccessManager->revokeGroupAccess($removedCategory, $group);
            }
        }

        foreach ($preservedCategoryCodes as $code) {
            $newAccessLevel = $this->getSubmittedHighestAccessLevel($permissions, $code);
            $currentAccessLevel = $categoriesByAccessLevel[$code] ?? null;

            if ($currentAccessLevel !== $newAccessLevel) {
                $category = $this->getRootCategoryReferenceFromCode->execute($code);
                $this->categoryAccessManager->grantAccess($category, $group, $newAccessLevel);
            }
        }
    }

    private function getHighestAccessLevel(CategoryAccessInterface $access): ?string
    {
        if ($access->isOwnItems()) {
            return Attributes::OWN_PRODUCTS;
        } elseif ($access->isEditItems()) {
            return Attributes::EDIT_ITEMS;
        } elseif ($access->isViewItems()) {
            return Attributes::VIEW_ITEMS;
        }

        return null;
    }

    private function getSubmittedHighestAccessLevel(array $permissions, string $categoryCode): string
    {
        if (in_array($categoryCode, $permissions['own']['identifiers'])) {
            return Attributes::OWN_PRODUCTS;
        } elseif (in_array($categoryCode, $permissions['edit']['identifiers'])) {
            return Attributes::EDIT_ITEMS;
        } else {
            return Attributes::VIEW_ITEMS;
        }
    }


    private function getCategoriesByAccessLevel(GroupInterface $group): array
    {
        $categoriesByAccessLevel = [];

        // could be improved with a query
        $categoriesAccesses = $this->categoryAccessManager->getAccessesByGroup($group);

        foreach ($categoriesAccesses as $access) {
            $highestAccessLevel = $this->getHighestAccessLevel($access);
            if (null === $highestAccessLevel) {
                continue;
            }
            $categoriesByAccessLevel[$access->getCategory()->getCode()] = $highestAccessLevel;
        }
        return $categoriesByAccessLevel;
    }

    /**
     * @param $group
     * @param array $permissions
     */
    private function updateDefaultPermissions($group, array $permissions): void
    {
        $defaultPermissions = $group->getDefaultPermissions();

        $currentHighestAll = $this->getCurrentHighestAll($defaultPermissions);
        $submittedHighestAll = $this->getSubmittedHighestAll($permissions);

        if (($defaultPermissions['category_own'] ?? false) !== $permissions['own']['all']) {
            $group->setDefaultPermission('category_own', true);

        }
        if (($defaultPermissions['category_edit'] ?? false) !== $permissions['edit']['all']) {
            $group->setDefaultPermission('category_edit', true);
        }
        if (($defaultPermissions['category_view'] ?? false) !== $permissions['view']['all']) {
            $group->setDefaultPermission('category_view', true);
        }
    }

    private function getCurrentHighestAll($defaultPermission): string
    {
        if (true === ($defaultPermission['category_own'] ?? null)) {
            return  Attributes::OWN_PRODUCTS;
        } else if (true === ($defaultPermission['category_edit'] ?? null)) {
            return Attributes::EDIT_ITEMS;
        } else {
            return Attributes::VIEW_ITEMS;
        }
    }

    private function getSubmittedHighestAll($permissions): string
    {
        if (true === $permissions['own']['all']) {
            return  Attributes::OWN_PRODUCTS;
        } else if (true === $permissions['edit']['all']) {
            return Attributes::EDIT_ITEMS;
        } else {
            return Attributes::VIEW_ITEMS;
        }
    }
}
