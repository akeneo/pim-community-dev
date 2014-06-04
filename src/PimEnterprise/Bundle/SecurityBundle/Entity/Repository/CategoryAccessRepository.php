<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;

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
        $accessField = ($accessLevel === CategoryVoter::EDIT_PRODUCTS) ? 'editProducts' : 'viewProducts';

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.category = :category')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
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
            ->andWhere($qb->expr()->eq($this->getAccessField($accessLevel), true))
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
        // prepare access field depending on access level
        $accessField = ($accessLevel === CategoryVoter::EDIT_PRODUCTS)
            ? 'ca.edit_products'
            : 'ca.view_products';

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
                        $qb->expr()->neq($accessField, true),
                        $qb->expr()->in('ca.role_id', $roleIds)
                    ),
                    $qb->expr()->isNull($accessField)
                )
            )
            ->groupBy('c.id');

        return $qb;
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
     * Get granted category ids for a user
     * If $filterableIds is provided, the returned ids will consist of these ids
     * filtered by the given access level
     *
     * @param User      $user
     * @param string    $accessLevel
     * @param integer[] $filterableIds
     *
     * @return integer[]
     */
    public function getGrantedAttributeIds(User $user, $accessLevel, array $filterableIds = null)
    {
        $qb = $this->getGrantedCategoryQB($user, $accessLevel);
        $qb
            ->select('a.id')
            ->innerJoin('ca.products', 'a')
            ->groupBy('a.id');

        if (null !== $filterableIds) {
            $qb->andWhere(
                $qb->expr()->in('a.id', $filterableIds)
            );
        }

        return $this->hydrateAsIds($qb);
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
        return ($accessLevel === CategoryVoter::EDIT_PRODUCTS)
            ? 'ca.editProducts'
            : 'ca.viewProducts';
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
     * @param string      $entityParameter
     * @param string|null $targetEntity
     *
     * @return string
     */
    protected function getTableName($classParam, $targetEntity = null)
    {
        return $this->tableNameBuilder->getTableName($classParam, $targetEntity);
    }
}
