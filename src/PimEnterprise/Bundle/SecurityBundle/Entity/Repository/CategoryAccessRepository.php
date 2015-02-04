<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Category access repository
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryAccessRepository extends EntityRepository
{
    /**
     * @var TableNameBuilder
     */
    protected $tableNameBuilder;

    /**
     * Get user groups that have the specified access to a category
     *
     * @param CategoryInterface $category
     * @param string            $accessLevel
     *
     * @return Group[]
     */
    public function getGrantedUserGroups(CategoryInterface $category, $accessLevel)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('g')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->where('a.category = :category')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $this->getAccessField($accessLevel)), true))
            ->setParameter('category', $category);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a category
     * If excluded user groups are provided, access will not be revoked for these group
     *
     * @param CategoryInterface $category
     * @param Group[]           $excludedGroups
     *
     * @return int
     */
    public function revokeAccess(CategoryInterface $category, array $excludedGroups = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.category = :category')
            ->setParameter('category', $category);

        if (!empty($excludedGroups)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.userGroup', ':excludedGroups'))
                ->setParameter('excludedGroups', $excludedGroups);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Remove access to a group for all categories
     *
     * @param Group[] $groups
     *
     * @return int
     */
    public function revokeAccessToGroups(array $groups)
    {
        $qb = $this->createQueryBuilder('a');

        $qb
            ->delete()
            ->where($qb->expr()->in('a.userGroup', ':groups'))
            ->setParameter('groups', $groups);

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
            ->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->resetDQLParts(['select'])
            ->innerJoin('ca.category', 'c', 'c.id')
            ->select('c.id');

        return $qb;
    }

    /**
     * Get granted categories QB from the provided QB.
     * The provided QB will be used to generate a subquery from which
     * only granted categories will be extracted.
     *
     * @param QueryBuilder $categoryQB
     * @param User         $user
     * @param string       $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedCategoryIdsFromQB(QueryBuilder $categoryQB, User $user, $accessLevel)
    {
        $categoryRootAlias = current($categoryQB->getRootAliases());

        $categoryQB->resetDQLParts(['select']);
        $categoryQB->select($categoryRootAlias.'.id');

        $qb = $this->createQueryBuilder('ca');
        $qb
            ->select('DISTINCT c.id')
            ->innerJoin('ca.category', 'c', 'c.id')
            ->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->andWhere($qb->expr()->in('c.id', $categoryQB->getDQL()));

        $qb->setParameter('groups', $user->getGroups()->toArray());

        foreach ($categoryQB->getParameters() as $param) {
            $qb->getParameters()->add($param);
        }

        return $this->hydrateAsIds($qb);
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
        // get group ids
        $groupIds = array_map(
            function (Group $group) {
                return $group->getId();
            },
            $user->getGroups()->toArray()
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
                        $qb->expr()->in('ca.user_group_id', $groupIds)
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
     * @param Group[]   $groups
     * @param integer[] $categoryIds
     *
     * @return integer[]
     */
    public function getCategoryIdsWithExistingAccess($groups, $categoryIds)
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $groups)
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
     * Get the granted user groups for a product
     *
     * @param ProductInterface $product     the product
     * @param string           $accessLevel the expected access level
     *
     * @return array
     */
    public function getGrantedUserGroupsForProduct(ProductInterface $product, $accessLevel)
    {
        $categories = $product->getCategories();
        if (count($categories) === 0) {
            return [];
        }
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }
        $qb = $this->createQueryBuilder('ca');
        $qb->where($qb->expr()->in('ca.category', $categoryIds));
        $qb->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true));
        $qb->leftJoin('ca.userGroup', 'ug');
        $qb->select('DISTINCT (ug.id) as id, ug.name');
        $groups = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        return $groups;
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
                $qb->expr()->in('o.userGroup', ':groups')
            )
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('o.'.$this->getAccessField(Attributes::OWN_PRODUCTS), true))
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);

        return (bool) count($result);
    }

    /**
     * Check if categories are granted to user
     *
     * @param User   $user
     * @param string $accessLevel
     * @param array  $categoryIds
     *
     * @throws \LogicException
     *
     * @return true
     */
    public function isCategoriesGranted(User $user, $accessLevel, array $categoryIds)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from($this->_entityName, 'ca');

        $qb->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->andWhere($qb->expr()->in('c.id', $categoryIds))
            ->innerJoin('ca.category', 'c', 'c.id');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @return string
     *
     * @throws \LogicException
     */
    protected function getAccessField($accessLevel)
    {
        $mapping = [
            Attributes::OWN_PRODUCTS => 'ownProducts',
            Attributes::EDIT_PRODUCTS => 'editProducts',
            Attributes::VIEW_PRODUCTS => 'viewProducts',
        ];
        if (!isset($mapping[$accessLevel])) {
            throw new \LogicException(sprintf('"%s" access level does not exist', $accessLevel));
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
