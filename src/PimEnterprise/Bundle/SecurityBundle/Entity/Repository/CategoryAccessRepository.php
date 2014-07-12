<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Category access repository
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryAccessRepository extends EntityRepository
{
    /**
     * @var TableNameBuilder
     */
    protected $tableNameBuilder;

    /**
     * Get roles that have the specified access to a category
     *
     * @param CategoryInterface $category
     * @param string            $accessLevel
     *
     * @return Role[]
     */
    public function getGrantedRoles(CategoryInterface $category, $accessLevel)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.category = :category')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $this->getAccessField($accessLevel)), true))
            ->setParameter('category', $category);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a category
     * If excluded roles are provided, access will not be revoked for these roles
     *
     * @param CategoryInterface $category
     * @param Role[]            $excludedRoles
     *
     * @return integer
     */
    public function revokeAccess(CategoryInterface $category, array $excludedRoles = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.category = :category')
            ->setParameter('category', $category);

        if (!empty($excludedRoles)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.role', ':excludedRoles'))
                ->setParameter('excludedRoles', $excludedRoles);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get granted category query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedCategoryQB(User $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->andWhere($qb->expr()->in('ca.role', ':roles'))
            ->setParameter('roles', $user->getRoles())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->resetDQLParts(['select'])
            ->innerJoin('ca.category', 'c', 'c.id')
            ->select('c.id');

        return $qb;
    }

    /**
     * Get revoked category query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getRevokedCategoryQB(User $user, $accessLevel)
    {
        // get role ids
        $roleIds = array_map(
            function (RoleInterface $role) {
                return $role->getId();
            },
            $user->getRoles()
        );

        $categoryTable = $this->getTableName('pim_catalog.entity.category.class');
        $categoryAccessTable = $this->getTableName('pimee_security.entity.category_access.class');

        $conn = $this->_em->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb
            ->select('*')
            ->from($categoryTable, 'c')
            ->leftJoin('c', $categoryAccessTable, 'ca', 'ca.category_id = c.id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->neq('ca.'.$this->getAccessField($accessLevel), true),
                        $qb->expr()->in('ca.role_id', $roleIds)
                    ),
                    $qb->expr()->isNull('ca.'.$this->getAccessField($accessLevel))
                )
            )
            ->groupBy('c.id');

        return $qb;
    }

    /**
     * Get granted category ids
     *
     * @param RoleInterface[] $roles
     * @param integer[]       $categoryIds
     *
     * @return integer[]
     */
    public function getCategoryIdsWithExistingAccess($roles, $categoryIds)
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->andWhere($qb->expr()->in('ca.role', ':roles'))
            ->setParameter('roles', $roles)
            ->andWhere($qb->expr()->in('c.id', ':categories'))
            ->setParameter('categories', $categoryIds)
            ->resetDQLParts(['select'])
            ->innerJoin('ca.category', 'c', 'c.id')
            ->select('c.id');

        return $this->hydrateAsIds($qb);
    }

    /**
     * Returns granted categories ids
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getGrantedCategoryIds(User $user, $accessLevel)
    {
        $qb = $this->getGrantedCategoryQB($user, $accessLevel);

        return $this->hydrateAsIds($qb);
    }

    /**
     * Returns revoked category ids
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedCategoryIds(User $user, $accessLevel)
    {
        $qb = $this->getRevokedCategoryQB($user, $accessLevel);
        $qb->select('c.id');

        return array_map(
            function ($row) {
                return $row['id'];
            },
            $qb->execute()->fetchAll()
        );
    }

    /**
     * Returns revoked category ids
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedAttributeIds(User $user, $accessLevel)
    {
        $attTable = $this->getTableName('pim_catalog.entity.category.class');

        $qb = $this->getRevokedCategoryQB($user, $accessLevel);
        $qb
            ->select('a.id')
            ->innerJoin('c', $attTable, 'a', 'a.category_id = c.id')
            ->groupBy('a.id');

        return array_map(
            function ($row) {
                return $row['id'];
            },
            $qb->execute()->fetchAll()
        );
    }

    /**
     * Get the granted roles for a product
     *
     * @param ProductInterface $product     the product
     * @param string           $accessLevel the expected access level
     *
     * @return array
     */
    public function getGrantedRolesForProduct(ProductInterface $product, $accessLevel)
    {
        $categories = $product->getCategories();
        if (count($categories) === 0) {
            return [];
        }
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[]= $category->getId();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where($qb->expr()->in('o.category', $categoryIds));
        $qb->andWhere($qb->expr()->eq('o.'.$this->getAccessField($accessLevel), true));
        $qb->leftJoin('o.role', 'role');
        $qb->select('DISTINCT(role.id) as id, role.label');
        $roles = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        return $roles;
    }

    /**
     * Indicates whether a user is the owner of any categories
     *
     * @param User $user
     *
     * @return boolean
     */
    public function isOwner(User $user)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('o.id')
            ->where(
                $qb->expr()->in('o.role', ':roles')
            )
            ->setParameter('roles', $user->getRoles())
            ->andWhere($qb->expr()->eq('o.'.$this->getAccessField(Attributes::OWN_PRODUCTS), true))
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);

        return (bool) count($result);
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @return string
     */
    protected function getAccessField($accessLevel)
    {
        $mapping = [
            Attributes::OWN_PRODUCTS => 'ownProducts',
            Attributes::EDIT_PRODUCTS => 'editProducts',
            Attributes::VIEW_PRODUCTS => 'viewProducts'
        ];
        if (!isset($mapping[$accessLevel])) {
            throw new \LogicException(sprintf('%s access level not exists', $accessLevel));
        }

        return $mapping[$accessLevel];
    }

    /**
     * Execute a query builder and hydrate it as an array of database identifiers
     *
     * @param QueryBuilder $qb
     *
     * @return integer[]
     */
    protected function hydrateAsIds(QueryBuilder $qb)
    {
        return array_map(
            function ($row) {
                return $row['id'];
            },
            $qb->getQuery()->getArrayResult()
        );
    }

    /**
     * Set table name builder
     *
     * @param TableNameBuilder $tableNameBuilder
     *
     * @return CategoryAccessRepository
     */
    public function setTableNameBuilder(TableNameBuilder $tableNameBuilder)
    {
        $this->tableNameBuilder = $tableNameBuilder;

        return $this;
    }

    /**
     * Get table name of entity defined
     *
     * @param string      $classParam
     * @param string|null $targetEntity
     *
     * @return string
     */
    protected function getTableName($classParam, $targetEntity = null)
    {
        return $this->tableNameBuilder->getTableName($classParam, $targetEntity);
    }
}
