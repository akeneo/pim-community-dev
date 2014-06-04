<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

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
     * Constructor
     *
     * @param ManagerRegistry   $registry
     * @param string            $categoryAccessClass
     */
    public function __construct(ManagerRegistry $registry, $categoryAccessClass)
    {
        $this->registry            = $registry;
        $this->categoryAccessClass = $categoryAccessClass;
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
        return $this->getRepository()->getGrantedRoles($category, CategoryVoter::VIEW_PRODUCTS);
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
        return $this->getRepository()->getGrantedRoles($category, CategoryVoter::EDIT_PRODUCTS);
    }

    /**
     * Grant access on a category to specified roles
     *
     * @param CategoryInterface $category
     * @param Role[]            $viewRoles
     * @param Role[]            $editRoles
     */
    public function setAccess(CategoryInterface $category, $viewRoles, $editRoles)
    {
        $grantedRoles = [];
        foreach ($editRoles as $role) {
            $this->grantAccess($category, $role, CategoryVoter::EDIT_PRODUCTS);
            $grantedRoles[] = $role;
        }

        foreach ($viewRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($category, $role, CategoryVoter::VIEW_PRODUCTS);
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($category, $grantedRoles);
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
            ->setEditProducts($accessLevel === CategoryVoter::EDIT_PRODUCTS);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Get CategoryeAccess entity for a category and role
     *
     * @param CategoryInterface $category
     * @param Role              $role
     *
     * @return CategoryAccessInterface
     */
    protected function getCategoryAccess(CategoryInterface $category, Role $role)
    {
        $access = $this->getRepository()
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
        return $this->getRepository()->revokeAccess($category, $excludedRoles);
    }

    /**
     * Get repository
     *
     * @return CategoryAccessRepository
     */
    protected function getRepository()
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
