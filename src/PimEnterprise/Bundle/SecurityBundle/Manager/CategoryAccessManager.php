<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Category access manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryAccessManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $categoryAccessClass;

    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $categoryAccessClass
     * @param string          $categoryClass
     */
    public function __construct(ManagerRegistry $registry, $categoryAccessClass, $categoryClass)
    {
        $this->registry            = $registry;
        $this->categoryAccessClass = $categoryAccessClass;
        $this->categoryClass       = $categoryClass;
    }

    /**
     * Get roles that have view access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Role[]
     */
    public function getViewRoles(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedRoles($category, Attributes::VIEW_PRODUCTS);
    }

    /**
     * Get roles that have edit access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Role[]
     */
    public function getEditRoles(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedRoles($category, Attributes::EDIT_PRODUCTS);
    }

    /**
     * Get roles that have own access to a category
     *
     * @param CategoryInterface $category
     *
     * @return Role[]
     */
    public function getOwnRoles(CategoryInterface $category)
    {
        return $this->getAccessRepository()->getGrantedRoles($category, Attributes::OWN_PRODUCTS);
    }

    /**
     * Grant access on a category to specified roles, own implies edit which implies read
     *
     * @param CategoryInterface $category  the category
     * @param Role[]            $viewRoles the view roles
     * @param Role[]            $editRoles the edit roles
     * @param Role[]            $ownRoles  the own roles
     */
    public function setAccess(CategoryInterface $category, $viewRoles, $editRoles, $ownRoles)
    {
        $grantedRoles = [];
        foreach ($ownRoles as $role) {
            $this->grantAccess($category, $role, Attributes::OWN_PRODUCTS);
            $grantedRoles[] = $role;
        }

        foreach ($editRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($category, $role, Attributes::EDIT_PRODUCTS);
                $grantedRoles[] = $role;
            }
        }

        foreach ($viewRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($category, $role, Attributes::VIEW_PRODUCTS);
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($category, $grantedRoles);
        $this->getObjectManager()->flush();
    }

    /**
     * Update accesses to all category children to specified roles
     *
     * @param CategoryInterface $parent
     * @param Role[]            $addViewRoles
     * @param Role[]            $addEditRoles
     * @param Role[]            $addOwnRoles
     * @param Role[]            $removeViewRoles
     * @param Role[]            $removeEditRoles
     * @param Role[]            $removeOwnRoles
     */
    public function updateChildrenAccesses(
        CategoryInterface $parent,
        $addViewRoles,
        $addEditRoles,
        $addOwnRoles,
        $removeViewRoles,
        $removeEditRoles,
        $removeOwnRoles
    ) {
        $mergedPermissions = $this->getMergedPermissions(
            $addViewRoles,
            $addEditRoles,
            $addOwnRoles,
            $removeViewRoles,
            $removeEditRoles,
            $removeOwnRoles
        );

        $codeToRoles = [];
        $allRoles = array_merge(
            $addViewRoles,
            $addEditRoles,
            $addOwnRoles,
            $removeViewRoles,
            $removeEditRoles,
            $removeOwnRoles
        );
        foreach ($allRoles as $role) {
            $codeToRoles[$role->getRole()] = $role;
        }

        $categoryRepo = $this->getCategoryRepository();
        $childrenIds = $categoryRepo->getAllChildrenIds($parent);

        foreach ($codeToRoles as $role) {
            $roleCode = $role->getRole();
            $view = $mergedPermissions[$roleCode]['view'];
            $edit = $mergedPermissions[$roleCode]['edit'];
            $own = $mergedPermissions[$roleCode]['own'];

            $accessRepo = $this->getAccessRepository();
            $toUpdateIds = $accessRepo->getCategoryIdsWithExistingAccess([$role], $childrenIds);
            $toAddIds = array_diff($childrenIds, $toUpdateIds);

            if ($view === false && $edit === false && $own === false) {
                $this->removeAccesses($toUpdateIds, $role);
            } else {
                if (count($toAddIds) > 0) {
                    $this->addAccesses($toAddIds, $role, $view, $edit, $own);
                }
                if (count($toUpdateIds) > 0) {
                    $this->updateAccesses($toUpdateIds, $role, $view, $edit, $own);
                }
            }
        }
    }

    /**
     * Get merged permissions
     *
     * @param Role[] $addViewRoles
     * @param Role[] $addEditRoles
     * @param Role[] $addOwnRoles
     * @param Role[] $removeViewRoles
     * @param Role[] $removeEditRoles
     * @param Role[] $removeOwnRoles
     *
     * @return array
     */
    protected function getMergedPermissions(
        $addViewRoles,
        $addEditRoles,
        $addOwnRoles,
        $removeViewRoles,
        $removeEditRoles,
        $removeOwnRoles
    ) {
        $mergedPermissions = [];
        $allRoles = array_merge(
            $addViewRoles, $addEditRoles, $addOwnRoles, $removeViewRoles, $removeEditRoles, $removeOwnRoles
        );
        foreach ($allRoles as $role) {
            $mergedPermissions[$role->getRole()] = ['view' => null, 'edit' => null, 'own' => null];
        }
        foreach ($addViewRoles as $role) {
            $mergedPermissions[$role->getRole()]['view'] = true;
        }
        foreach ($addEditRoles as $role) {
            $mergedPermissions[$role->getRole()]['edit'] = true;
            $mergedPermissions[$role->getRole()]['view'] = true;
        }
        foreach ($addOwnRoles as $role) {
            $mergedPermissions[$role->getRole()]['own']  = true;
            $mergedPermissions[$role->getRole()]['edit'] = true;
            $mergedPermissions[$role->getRole()]['view'] = true;
        }

        foreach ($removeViewRoles as $role) {
            $mergedPermissions[$role->getRole()]['view'] = false;
        }
        foreach ($removeEditRoles as $role) {
            $mergedPermissions[$role->getRole()]['edit'] = false;
        }
        foreach ($removeOwnRoles as $role) {
            $mergedPermissions[$role->getRole()]['own'] = false;
        }

        return $mergedPermissions;
    }

    /**
     * Delete accesses on categories
     *
     * @param integer[] $categoryIds
     * @param Role      $role
     */
    protected function removeAccesses($categoryIds, Role $role)
    {
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'role' => $role]);

        foreach ($accesses as $access) {
            $this->getObjectManager()->remove($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Add accesses on categories, a null permission will be resolved as false
     *
     * @param integer[]    $categoryIds
     * @param Role         $role
     * @param boolean|null $view
     * @param boolean|null $edit
     * @param boolean|null $own
     */
    protected function addAccesses($categoryIds, Role $role, $view = false, $edit = false, $own = false)
    {
        $view = ($view === null) ? false : $view;
        $edit = ($edit === null) ? false : $edit;
        $own = ($own === null) ? false : $own;
        $categories = $this->getCategoryRepository()->findBy(['id' => $categoryIds]);

        foreach ($categories as $category) {
            $access = new $this->categoryAccessClass();
            $access->setCategory($category)->setRole($role);
            $access->setViewProducts($view);
            $access->setEditProducts($edit);
            $access->setOwnProducts($own);
            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Update accesses on categories, if a permission is null we don't update
     *
     * @param integer[]    $categoryIds
     * @param Role         $role
     * @param boolean|null $view
     * @param boolean|null $edit
     * @param boolean|null $own
     */
    protected function updateAccesses($categoryIds, Role $role, $view = false, $edit = false, $own = false)
    {
        $accesses = $this->getAccessRepository()->findBy(['category' => $categoryIds, 'role' => $role]);

        foreach ($accesses as $access) {
            if ($view !== null) {
                $access->setViewProducts($view);
            }
            if ($edit !== null) {
                $access->setEditProducts($edit);
            }
            if ($own !== null) {
                $access->setOwnProducts($own);
            }
            $this->getObjectManager()->persist($access);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on a category for the provided role
     *
     * @param CategoryInterface $category
     * @param Role              $role
     * @param string            $accessLevel
     */
    public function grantAccess(CategoryInterface $category, Role $role, $accessLevel)
    {
        $access = $this->getCategoryAccess($category, $role);
        $access
            ->setViewProducts(true)
            ->setEditProducts(in_array($accessLevel, [Attributes::EDIT_PRODUCTS, Attributes::OWN_PRODUCTS]))
            ->setOwnProducts($accessLevel === Attributes::OWN_PRODUCTS);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Get CategoryAccess entity for a category and role
     *
     * @param CategoryInterface $category
     * @param Role              $role
     *
     * @return CategoryAccessInterface
     */
    protected function getCategoryAccess(CategoryInterface $category, Role $role)
    {
        $access = $this->getAccessRepository()
            ->findOneBy(
                [
                    'category' => $category,
                    'role'     => $role
                ]
            );

        if (!$access) {
            $access = new $this->categoryAccessClass();
            $access
                ->setCategory($category)
                ->setRole($role);
        }

        return $access;
    }

    /**
     * Revoke access to a category
     * If $excludedRoles are provided, access will not be revoked for roles with them
     *
     * @param CategoryInterface $category
     * @param Role[]            $excludedRoles
     *
     * @return integer
     */
    protected function revokeAccess(CategoryInterface $category, array $excludedRoles = [])
    {
        return $this->getAccessRepository()->revokeAccess($category, $excludedRoles);
    }

    /**
     * Get category repository
     *
     * @return CategoryRepository
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
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->categoryAccessClass);
    }
}
