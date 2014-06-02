<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess;
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
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
        $grantedRoles = array();
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
        $this->objectManager->flush();
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

        $this->objectManager->persist($access);
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
            //TODO: use a parameter to get the classname
            $access = new CategoryAccess();
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
        return $this->objectManager->getRepository('PimEnterpriseSecurityBundle:CategoryAccess');
    }
}
