<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoriesReferencesFromCodes;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Model\CategoryAccessInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;

class UserGroupCategoryPermissionsSaver
{
    private CategoryAccessManager $categoryAccessManager;
    private GroupRepository $groupRepository;
    private SaverInterface $groupSaver;
    private CategoryRepositoryInterface $categoryRepository;
    private GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes;

    public function __construct(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        CategoryRepositoryInterface $categoryRepository,
        GetRootCategoriesReferencesFromCodes $getRootCategoriesReferencesFromCodes
    ) {
        $this->categoryAccessManager = $categoryAccessManager;
        $this->groupRepository = $groupRepository;
        $this->groupSaver = $groupSaver;
        $this->categoryRepository = $categoryRepository;
        $this->getRootCategoriesReferencesFromCodes = $getRootCategoriesReferencesFromCodes;
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

        $preservedCategoryCodes = $permissions['view']['identifiers'];
        $removedCategoryCodes = array_diff(array_keys($categoriesByAccessLevel), $preservedCategoryCodes);

        if (!empty($removedCategoryCodes)) {
            $removedCategories = $this->getRootCategoriesReferencesFromCodes->execute($removedCategoryCodes);

            foreach ($removedCategories as $removedCategory) {
                $this->categoryAccessManager->revokeGroupAccess($removedCategory, $group);
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
}
