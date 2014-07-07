<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
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
        return $this->getAccessRepository()->getGrantedRoles($category, CategoryVoter::VIEW_PRODUCTS);
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
        return $this->getAccessRepository()->getGrantedRoles($category, CategoryVoter::EDIT_PRODUCTS);
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
     * Add accesses to all category children to specified roles
     *
     * @param CategoryInterface $parent
     * @param Role[]            $viewRoles
     * @param Role[]            $editRoles
     */
    public function addChildrenAccess(CategoryInterface $parent, $viewRoles, $editRoles)
    {
        $categoryRepo = $this->getCategoryRepository();
        $categoryQb = $categoryRepo->getAllChildrenQueryBuilder($parent);
        $rootAlias  = current($categoryQb->getRootAliases());
        $rootEntity = current($categoryQb->getRootEntities());
        $categoryQb->select($rootAlias.'.id');
        $categoryQb->resetDQLPart('from');
        $categoryQb->from($rootEntity, $rootAlias, $rootAlias.'.id');
        $childrenIds = array_keys($categoryQb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY));

        // TODO : how to deal with view but not edit ? if a row exist already for view ?
        $role = current($viewRoles);
        $accessRepo = $this->getAccessRepository();
        $grantedIds = $accessRepo->getGrantedCategoryIdsByRoles([$role], CategoryVoter::VIEW_PRODUCTS, $childrenIds);

        $toGrantIds = array_diff($childrenIds, $grantedIds);
        $categories = $categoryRepo->findBy(['id' => $toGrantIds]);
        foreach ($categories as $category) {
            $access = new $this->categoryAccessClass();
            $access->setCategory($category)->setRole($role);
            $access->setViewProducts(true);
            $access->setEditProducts(true);
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
            ->setEditProducts($accessLevel === CategoryVoter::EDIT_PRODUCTS);

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
