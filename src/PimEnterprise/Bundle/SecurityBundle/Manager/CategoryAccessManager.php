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

use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Model\CategoryAccessInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category access manager
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryAccessManager
{
    /** @var CategoryAccessRepository */
    protected $accessRepository;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var GroupRepository */
    protected $groupRepository;

    /** @var BulkSaverInterface */
    protected $accessSaver;

    /** @var BulkRemoverInterface */
    protected $accessRemover;

    /** @var string */
    protected $categoryAccessClass;

    /**
     * @param CategoryAccessRepository    $accessRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param GroupRepository             $groupRepository
     * @param BulkSaverInterface          $accessSaver
     * @param BulkRemoverInterface        $accessRemover
     * @param string                      $categoryAccessClass
     */
    public function __construct(
        CategoryAccessRepository $accessRepository,
        CategoryRepositoryInterface $categoryRepository,
        GroupRepository $groupRepository,
        BulkSaverInterface $accessSaver,
        BulkRemoverInterface $accessRemover,
        $categoryAccessClass
    ) {
        $this->accessRepository = $accessRepository;
        $this->categoryRepository = $categoryRepository;
        $this->groupRepository = $groupRepository;
        $this->accessSaver = $accessSaver;
        $this->accessRemover = $accessRemover;
        $this->categoryAccessClass = $categoryAccessClass;
    }

    /**
     * Get user groups that have view access to a category
     *
     * @param CategoryInterface $category
     *
     * @return GroupInterface[]
     */
    public function getViewUserGroups(CategoryInterface $category)
    {
        return $this->accessRepository->getGrantedUserGroups($category, Attributes::VIEW_ITEMS);
    }

    /**
     * Get user groups that have edit access to a category
     *
     * @param CategoryInterface $category
     *
     * @return GroupInterface[]
     */
    public function getEditUserGroups(CategoryInterface $category)
    {
        return $this->accessRepository->getGrantedUserGroups($category, Attributes::EDIT_ITEMS);
    }

    /**
     * Get user groups that have own access to a category
     *
     * @param CategoryInterface $category
     *
     * @return GroupInterface[]
     */
    public function getOwnUserGroups(CategoryInterface $category)
    {
        return $this->accessRepository->getGrantedUserGroups($category, Attributes::OWN_PRODUCTS);
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
        switch (true) {
            case Attributes::VIEW_ITEMS === $attribute:
                $grantedUserGroups = $this->getViewUserGroups($category);
                break;

            case Attributes::EDIT_ITEMS === $attribute:
                $grantedUserGroups = $this->getEditUserGroups($category);
                break;

            case Attributes::OWN_PRODUCTS === $attribute:
                $grantedUserGroups = $this->getOwnUserGroups($category);
                break;

            default:
                throw new \LogicException(sprintf('Attribute "%" is not supported.', $attribute));
                break;
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
     * @param GroupInterface[]  $viewGroups the view user groups
     * @param GroupInterface[]  $editGroups the edit user groups
     * @param GroupInterface[]  $ownGroups  the own user groups
     */
    public function setAccess(CategoryInterface $category, $viewGroups, $editGroups, $ownGroups)
    {
        $grantedGroups = [];
        $grantedAccesses = [];
        foreach ($ownGroups as $group) {
            $grantedAccesses[] = $this->buildGrantAccess($category, $group, Attributes::OWN_PRODUCTS);
            $grantedGroups[] = $group;
        }

        foreach ($editGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $grantedAccesses[] = $this->buildGrantAccess($category, $group, Attributes::EDIT_ITEMS);
                $grantedGroups[] = $group;
            }
        }

        foreach ($viewGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $grantedAccesses[] = $this->buildGrantAccess($category, $group, Attributes::VIEW_ITEMS);
                $grantedGroups[] = $group;
            }
        }

        if (null !== $category->getId()) {
            $this->revokeAccess($category, $grantedGroups);
        }
        $this->accessSaver->saveAll($grantedAccesses);
    }

    /**
     * Set the accesses of a category like its parent.
     *
     * @param CategoryInterface $category
     * @param array             $options
     */
    public function setAccessLikeParent(CategoryInterface $category, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configure($resolver);

        $options = $resolver->resolve($options);

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
                (true === $options['owner']) ? $this->getOwnUserGroups($ancestor) : []
            );
        } else {
            // it a category from a new tree, let's put ALL permissions
            $defaultUserGroup = $this->groupRepository->getDefaultUserGroup();
            $this->setAccess(
                $category,
                [$defaultUserGroup],
                [$defaultUserGroup],
                (!isset($options['owner']) || true === $options['owner']) ? [$defaultUserGroup] : []
            );
        }
    }

    /**
     * Update accesses to all category children to specified user groups
     *
     * @param CategoryInterface $parent
     * @param GroupInterface[]  $addViewGroups
     * @param GroupInterface[]  $addEditGroups
     * @param GroupInterface[]  $addOwnGroups
     * @param GroupInterface[]  $removeViewGroups
     * @param GroupInterface[]  $removeEditGroups
     * @param GroupInterface[]  $removeOwnGroups
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

        /** @var GroupInterface[] $codeToGroups */
        $codeToGroups = [];

        /** @var GroupInterface[] $allGroups */
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

        $categoryRepo = $this->categoryRepository;
        $childrenIds = $categoryRepo->getAllChildrenIds($parent);

        foreach ($codeToGroups as $group) {
            $groupCode = $group->getName();
            $view = $mergedPermissions[$groupCode]['view'];
            $edit = $mergedPermissions[$groupCode]['edit'];
            $own = $mergedPermissions[$groupCode]['own'];

            $accessRepo = $this->accessRepository;
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
     * @param GroupInterface[] $addViewGroups
     * @param GroupInterface[] $addEditGroups
     * @param GroupInterface[] $addOwnGroups
     * @param GroupInterface[] $removeViewGroups
     * @param GroupInterface[] $removeEditGroups
     * @param GroupInterface[] $removeOwnGroups
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

        /** @var GroupInterface[] $allGroups */
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
            $mergedPermissions[$group->getName()]['own'] = true;
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
     * @param int[]          $categoryIds
     * @param GroupInterface $group
     */
    protected function removeAccesses($categoryIds, GroupInterface $group)
    {
        $accesses = $this->accessRepository->findBy(['category' => $categoryIds, 'userGroup' => $group]);
        $this->accessRemover->removeAll($accesses);
    }

    /**
     * Add accesses on categories, a null permission will be resolved as false
     *
     * @param int[]          $categoryIds
     * @param GroupInterface $group
     * @param bool|null      $view
     * @param bool|null      $edit
     * @param bool|null      $own
     */
    protected function addAccesses($categoryIds, GroupInterface $group, $view = false, $edit = false, $own = false)
    {
        $view = ($view === null) ? false : $view;
        $edit = ($edit === null) ? false : $edit;
        $own = ($own === null) ? false : $own;
        $categories = $this->categoryRepository->findBy(['id' => $categoryIds]);

        $grantAccesses = [];
        foreach ($categories as $category) {
            /** @var CategoryAccessInterface $access */
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setViewItems($view)
                ->setEditItems($edit)
                ->setOwnItems($own)
                ->setUserGroup($group);
            $grantAccesses[] = $access;
        }
        $this->accessSaver->saveAll($grantAccesses);
    }

    /**
     * Update accesses on categories, if a permission is null we don't update
     *
     * @param int[]          $categoryIds
     * @param GroupInterface $group
     * @param bool|null      $view
     * @param bool|null      $edit
     * @param bool|null      $own
     */
    protected function updateAccesses($categoryIds, GroupInterface $group, $view = false, $edit = false, $own = false)
    {
        /** @var CategoryAccessInterface[] $accesses */
        $accesses = $this->accessRepository->findBy(['category' => $categoryIds, 'userGroup' => $group]);
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
        }
        $this->accessSaver->saveAll($accesses);
    }

    /**
     * Grant specified access on a category for the provided user group
     *
     * @param CategoryInterface $category
     * @param GroupInterface    $group
     * @param string            $accessLevel
     */
    public function grantAccess(CategoryInterface $category, GroupInterface $group, $accessLevel)
    {
        $access = $this->buildGrantAccess($category, $group, $accessLevel);
        $this->accessSaver->saveAll([$access]);
    }

    /**
     * Build specified access on a category for the provided user group
     *
     * @param CategoryInterface $category
     * @param GroupInterface    $group
     * @param string            $accessLevel
     *
     * @return CategoryAccessInterface
     */
    protected function buildGrantAccess(CategoryInterface $category, GroupInterface $group, $accessLevel)
    {
        $access = $this->getCategoryAccess($category, $group);
        $access
            ->setViewItems(true)
            ->setEditItems(in_array($accessLevel, [Attributes::EDIT_ITEMS, Attributes::OWN_PRODUCTS]))
            ->setOwnItems($accessLevel === Attributes::OWN_PRODUCTS);

        return $access;
    }

    /**
     * Revoke access to a category
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param CategoryInterface $category
     * @param GroupInterface[]  $excludedGroups
     *
     * @return int
     */
    public function revokeAccess(CategoryInterface $category, array $excludedGroups = [])
    {
        return $this->accessRepository->revokeAccess($category, $excludedGroups);
    }

    /**
     * Get ProductCategoryAccess entity for a category and user group
     *
     * @param CategoryInterface $category
     * @param GroupInterface    $group
     *
     * @return CategoryAccessInterface
     */
    protected function getCategoryAccess(CategoryInterface $category, GroupInterface $group)
    {
        $access = $this->accessRepository
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
     * @param OptionsResolver $resolver
     */
    protected function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['owner' => true])
            ->setAllowedTypes('owner', 'boolean');
    }
}
