<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
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
    private CategoryAccessManager $categoryAccessManager;
    private GroupRepository $groupRepository;
    private SaverInterface $groupSaver;
    private GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes;
    private GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode;
    private GetRootCategoriesReferences $getRootCategoryReferences;

    public function __construct(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes,
        GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode,
        GetRootCategoriesReferences $getRootCategoryReferences
    ) {
        $this->categoryAccessManager = $categoryAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->getRootCategoriesReferencesFromCodes = $getRootCategoriesReferencesFromCodes;
        $this->getRootCategoryReferenceFromCode = $getRootCategoryReferenceFromCode;
        $this->getRootCategoryReferences = $getRootCategoryReferences;
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

        $manuallySelectedCategoriesCodes = array_unique(array_merge(
            $permissions['own']['identifiers'],
            $permissions['edit']['identifiers'],
            $permissions['view']['identifiers'],
        ));
        $removedCategoryCodes = array_diff(array_keys($categoriesByAccessLevel), $manuallySelectedCategoriesCodes);

        if (!empty($removedCategoryCodes)) {
            $removedCategories = $this->getRootCategoriesReferencesFromCodes->execute($removedCategoryCodes);

            foreach ($removedCategories as $removedCategory) {
                $this->categoryAccessManager->revokeGroupAccess($removedCategory, $group);
            }
        }

        foreach ($manuallySelectedCategoriesCodes as $code) {
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
        $manuallySelectedCategoriesCodes = array_values(array_unique(array_merge(
            $permissions['own']['identifiers'],
            $permissions['edit']['identifiers'],
            $permissions['view']['identifiers'],
        )));

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

        $group->setDefaultPermission('category_view', in_array($submittedHighestAll, [Attributes::OWN_PRODUCTS, Attributes::EDIT_ITEMS, Attributes::VIEW_ITEMS]));
        $group->setDefaultPermission('category_edit', in_array($submittedHighestAll, [Attributes::OWN_PRODUCTS, Attributes::EDIT_ITEMS]));
        $group->setDefaultPermission('category_own', $submittedHighestAll === Attributes::OWN_PRODUCTS);

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
        if (true === ($defaultPermission['category_own'] ?? null)) {
            return Attributes::OWN_PRODUCTS;
        } else if (true === ($defaultPermission['category_edit'] ?? null)) {
            return Attributes::EDIT_ITEMS;
        } else if (true === ($defaultPermission['category_view'] ?? null)) {
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
}
