<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Component\Classification\Repository\CategoryRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category access manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryAccessManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $categoryAccessClass;

    /** @var string */
    protected $categoryClass;

    /** @var string */
    protected $userGroupClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $categoryAccessClass
     * @param string          $categoryClass
     * @param string          $userGroupClass
     */
    public function __construct(ManagerRegistry $registry, $categoryAccessClass, $categoryClass, $userGroupClass)
    {
        $this->registry            = $registry;
        $this->categoryAccessClass = $categoryAccessClass;
        $this->categoryClass       = $categoryClass;
        $this->userGroupClass      = $userGroupClass;
    }

    /**
     * Get user groups that have view access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getViewUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::VIEW_PRODUCTS);
    }

    /**
     * Get user groups that have edit access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getEditUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::EDIT_PRODUCTS);
    }

    /**
     * Get user groups that have own access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Group[]
     */
    public function getOwnUserGroups(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedUserGroups($category, Attributes::OWN_PRODUCTS);
    }

    /**
     * Check if a user is granted to an attribute on a given attribute
     *
     * @param UserInterface     $user
     * @param CategoryInterface $category
     * @param string            $attribute
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isUserGranted(UserInterface $user, CategoryInterface $category, $attribute)
    {
        if (Attributes::EDIT_PRODUCTS === $attribute) {
            $grantedUserGroups = $this->getEditUserGroups($category);
        } elseif (Attributes::VIEW_PRODUCTS === $attribute) {
            $grantedUserGroups = $this->getViewUserGroups($category);
        } else {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $attribute));
        }

        foreach ($grantedUserGroups as $userGroup) {
            if ($user->hasGroup($userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grant access on a category to specified user groups, own implies edit which implies read
     *
     * @param CategoryInterface $category   the category
     * @param Group[]           $viewGroups the view user groups
     * @param Group[]           $editGroups the edit user groups
     * @param Group[]           $ownGroups  the own user groups
     * @param bool              $flush      whether to flush the object manager
     */
    public function setAccess(CategoryInterface $category, $viewGroups, $editGroups, $ownGroups, $flush = false)
    {
        $grantedGroups = [];
        foreach ($ownGroups as $group) {
            $this->grantAccess($category, $group, Attributes::OWN_PRODUCTS, $flush);
            $grantedGroups[] = $group;
        }

        foreach ($editGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $this->grantAccess($category, $group, Attributes::EDIT_PRODUCTS, $flush);
                $grantedGroups[] = $group;
            }
        }

        foreach ($viewGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $this->grantAccess($category, $group, Attributes::VIEW_PRODUCTS, $flush);
                $grantedGroups[] = $group;
            }
        }

        if (null !== $category->getId()) {
            $this->revokeAccess($category, $grantedGroups);
        }

        if (true === $flush) {
            $this->getObjectManager()->flush();
        }
    }

    /**
     * Set the accesses of a category like its parent.
     *
     * @param CategoryInterface $category
     * @param bool              $flush
     */
    public function setAccessLikeParent(CategoryInterface $category, $flush = false)
    {
        // in case we have several new nested categories, we need to find the first ancestor that is managed
        // (ie: that has an ID and so permissions)
        $current = $category;
        do {
            $ancestor = $current->getParent();
            $current = $ancestor;
        } while (null !== $ancestor && null === $ancestor->getId());

        if (null !== $ancestor && null !== $ancestor->getId()) {
            // let's copy the permissions of the parent
            $this->setAccess(
                $category,
                $this->getViewUserGroups($ancestor),
                $this->getEditUserGroups($ancestor),
                $this->getOwnUserGroups($ancestor),
                $flush
            );
        } else {
            // it a category from a new tree, let's put ALL permissions
            $defaultUserGroup = $this->getUserGroupRepository()->getDefaultUserGroup();
            $this->setAccess(
                $category,
                [$defaultUserGroup],
                [$defaultUserGroup],
                [$defaultUserGroup],
                $flush
            );
        }
    }

    /**
     * Update accesses to all category children to specified user groups
     *
     * @param CategoryInterface $parent
     * @param Group[]           $addViewGroups
     * @param Group[]           $addEditGroups
     * @param Group[]           $addOwnGroups
     * @param Group[]           $removeViewGroups
     * @param Group[]           $removeEditGroups
     * @param Group[]           $removeOwnGroups
     */
    public function updateChildrenAccesses(
        CategoryInterface $parent,
        $addViewGroups,
        $addEditGroups,
        $addOwnGroups,
        $removeViewGroups,
        $removeEditGroups,
        $removeOwnGroups
    ) {
        $mergedPermissions = $this->getMergedPermissions(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );

        /** @var Group[] $codeToGroups */
        $codeToGroups = [];

        /** @var Group[] $allGroups */
        $allGroups = array_merge(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
        foreach ($allGroups as $group) {
            $codeToGroups[$group->getName()] = $group;
        }

        $categoryRepo = $this->getCategoryRepository();
        $childrenIds = $categoryRepo->getAllChildrenIds($parent);

        foreach ($codeToGroups as $group) {
            $groupCode = $group->getName();
            $view = $mergedPermissions[$groupCode]['view'];
            $edit = $mergedPermissions[$groupCode]['edit'];
            $own = $mergedPermissions[$groupCode]['own'];

            $accessRepo = $this->getAccessRepository();
            $toUpdateIds = $accessRepo->getCategoryIdsWithExistingAccess([$group], $childrenIds);
            $toAddIds = array_diff($childrenIds, $toUpdateIds);

            if ($view === false && $edit === false && $own === false) {
                $this->removeAccesses($toUpdateIds, $group);
            } else {
                if (count($toAddIds) > 0) {
                    $this->addAccesses($toAddIds, $group, $view, $edit, $own);
                }
                if (count($toUpdateIds) > 0) {
                    $this->updateAccesses($toUpdateIds, $group, $view, $edit, $own);
                }
            }
        }
    }

    /**
     * Get merged permissions
     *
     * @param Group[] $addViewGroups
     * @param Group[] $addEditGroups
     * @param Group[] $addOwnGroups
     * @param Group[] $removeViewGroups
     * @param Group[] $removeEditGroups
     * @param Group[] $removeOwnGroups
     *
     * @return array
     */
    protected function getMergedPermissions(
        $addViewGroups,
        $addEditGroups,
        $addOwnGroups,
        $removeViewGroups,
        $removeEditGroups,
        $removeOwnGroups
    ) {
        $mergedPermissions = [];

        /** @var Group[] $allGroups */
        $allGroups = array_merge(
            $addViewGroups,
            $addEditGroups,
            $addOwnGroups,
            $removeViewGroups,
            $removeEditGroups,
            $removeOwnGroups
        );
        foreach ($allGroups as $group) {
            $mergedPermissions[$group->getName()] = ['view' => null, 'edit' => null, 'own' => null];
        }
        foreach ($addViewGroups as $group) {
            $mergedPermissions[$group->getName()]['view'] = true;
        }
        foreach ($addEditGroups as $group) {
            $mergedPermissions[$group->getName()]['edit'] = true;
            $mergedPermissions[$group->getName()]['view'] = true;
        }
        foreach ($addOwnGroups as $group) {
            $mergedPermissions[$group->getName()]['own']  = true;
            $mergedPermissions[$group->getName()]['edit'] = true;
            $mergedPermissions[$group->getName()]['view'] = true;
        }

        foreach ($removeViewGroups as $group) {
            $mergedPermissions[$group->getName()]['view'] = false;
        }
        foreach ($removeEditGroups as $group) {
            $mergedPermissions[$group->getName()]['edit'] = false;
        }
        foreach ($removeOwnGroups as $group) {
            $mergedPermissions[$group->getName()]['own'] = false;
        }

        return $mergedPermissions;
    }

    /**
     * Delete accesses on categories
     *
     * @param integer[] $categoryIds
     * @param Group     $group
     */
    protected function removeAccesses($categoryIds, Group $group)
    {
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'userGroup' => $group]);

        foreach ($accesses as $access) {
            $this->getObjectManager()->remove($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Add accesses on categories, a null permission will be resolved as false
     *
     * @param integer[] $categoryIds
     * @param Group     $group
     * @param bool|null $view
     * @param bool|null $edit
     * @param bool|null $own
     */
    protected function addAccesses($categoryIds, Group $group, $view = false, $edit = false, $own = false)
    {
        $view = ($view === null) ? false : $view;
        $edit = ($edit === null) ? false : $edit;
        $own = ($own === null) ? false : $own;
        $categories = $this->getCategoryRepository()->findBy(['id' => $categoryIds]);

        foreach ($categories as $category) {
            /** @var CategoryAccessInterface $access */
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setViewItems($view)
                ->setEditItems($edit)
                ->setOwnItems($own)
                ->setUserGroup($group);

            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Update accesses on categories, if a permission is null we don't update
     *
     * @param integer[] $categoryIds
     * @param Group     $group
     * @param bool|null $view
     * @param bool|null $edit
     * @param bool|null $own
     */
    protected function updateAccesses($categoryIds, Group $group, $view = false, $edit = false, $own = false)
    {
        /** @var CategoryAccessInterface[] $accesses */
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'userGroup' => $group]);

        foreach ($accesses as $access) {
            if ($view !== null) {
                $access->setViewItems($view);
            }
            if ($edit !== null) {
                $access->setEditItems($edit);
            }
            if ($own !== null) {
                $access->setOwnItems($own);
            }
            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on a category for the provided user group
     *
     * @param CategoryInterface $category
     * @param Group             $group
     * @param string            $accessLevel
     * @param bool              $flush
     */
    public function grantAccess(CategoryInterface $category, Group $group, $accessLevel, $flush = false)
    {
        $access = $this->getCategoryAccess($category, $group);
        $access
            ->setViewItems(true)
            ->setEditItems(in_array($accessLevel, [Attributes::EDIT_PRODUCTS, Attributes::OWN_PRODUCTS]))
            ->setOwnItems($accessLevel === Attributes::OWN_PRODUCTS);

        $this->getObjectManager()->persist($access);
        if (true === $flush) {
            $this->getObjectManager()->flush();
        }
    }

    /**
     * Get ProductCategoryAccess entity for a category and user group
     *
     * @param CategoryInterface $category
     * @param Group             $group
     *
     * @return CategoryAccessInterface
     */
    protected function getCategoryAccess(CategoryInterface $category, Group $group)
    {
        $access = $this->getAccessRepository()
            ->findOneBy(
                [
                    'category'  => $category,
                    'userGroup' => $group
                ]
            );

        if (!$access) {
            /** @var CategoryAccessInterface $access */
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setUserGroup($group);
        }

        return $access;
    }

    /**
     * Revoke access to a category
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param CategoryInterface $category
     * @param Group[]           $excludedGroups
     *
     * @return int
     */
    protected function revokeAccess(CategoryInterface $category, array $excludedGroups = [])
    {
        return $this->getAccessRepository()->revokeAccess($category, $excludedGroups);
    }

    /**
     * Get category repository
     *
     * @return CategoryRepositoryInterface
     */
    protected function getCategoryRepository()
    {
        return $this->registry->getRepository($this->categoryClass);
    }

    /**
     * Get category access repository
     *
     * @return CategoryAccessRepository
     */
    protected function getAccessRepository()
    {
        return $this->registry->getRepository($this->categoryAccessClass);
    }

    /**
     * @return \Pim\Bundle\UserBundle\Entity\Repository\GroupRepository
     */
    protected function getUserGroupRepository()
    {
        return $this->registry->getRepository($this->userGroupClass);
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->categoryAccessClass);
    }
}
