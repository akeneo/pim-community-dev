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
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category access repository
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 *
 * TODO: It must be entity agnostic for PIM-4292.
 */
class CategoryAccessRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
{
    /** @var TableNameBuilder */
    protected $tableNameBuilder;

    /**
     * Get user groups that have the specified access to a category
     *
     * @param CategoryInterface $category
     * @param string            $accessLevel
     *
     * @return GroupInterface[]
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
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedCategoryQB(UserInterface $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->resetDQLParts(['select'])
            ->innerJoin('ca.category', 'c', 'c.id')
            ->select('c.id')
            ->distinct(true);

        return $qb;
    }

    /**
     * Get granted categories ids from the provided category
     *
     * @param CategoryInterface $category
     * @param UserInterface     $user
     * @param string            $accessLevel
     *
     * @return array
     */
    public function getGrantedChildrenIds(CategoryInterface $category, UserInterface $user, $accessLevel)
    {
        $qb = $this->getGrantedChildrenQB($category, $user, $accessLevel);

        return $this->hydrateAsIds($qb);
    }

    /**
     * Get granted categories codes from the provided category
     *
     * @param CategoryInterface $category
     * @param UserInterface     $user
     * @param string            $accessLevel
     *
     * @return array
     */
    public function getGrantedChildrenCodes(CategoryInterface $category, UserInterface $user, $accessLevel)
    {
        $qb = $this->getGrantedChildrenQB($category, $user, $accessLevel)
            ->resetDQLParts(['select'])
            ->select('c.code');

        return array_map(
            function ($row) {
                return $row['code'];
            },
            $qb->getQuery()->getArrayResult()
        );
    }

    /**
     * Get revoked category query builder
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getRevokedCategoryQB(UserInterface $user, $accessLevel)
    {
        // get group ids
        $groupIds = array_map(
            function (Group $group) {
                return $group->getId();
            },
            $user->getGroups()->toArray()
        );

        $categoryTable = $this->getTableName('pim_catalog.entity.category.class');
        $categoryAccessTable = $this->getTableName('pimee_security.entity.product_category_access.class');

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
            );

        return $qb;
    }

    /**
     * Get granted category ids
     *
     * @param Group[] $groups
     * @param int[]   $categoryIds
     *
     * @return int[]
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
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return int[]
     */
    public function getGrantedCategoryIds(UserInterface $user, $accessLevel)
    {
        $qb = $this->getGrantedCategoryQB($user, $accessLevel);

        return $this->hydrateAsIds($qb);
    }

    /**
     * Returns granted category codes
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return string[]
     */
    public function getGrantedCategoryCodes(UserInterface $user, $accessLevel)
    {
        $qb = $this->getGrantedCategoryQB($user, $accessLevel)
            ->resetDQLParts(['select'])
            ->select('c.code');

        return array_map(
            function ($row) {
                return $row['code'];
            },
            $qb->getQuery()->getArrayResult()
        );
    }

    /**
     * Returns revoked category ids
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return int[]
     */
    public function getRevokedCategoryIds(UserInterface $user, $accessLevel)
    {
        $qb = $this->getRevokedCategoryQB($user, $accessLevel);
        $qb->select('DISTINCT c.id');

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
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return int[]
     */
    public function getRevokedAttributeIds(UserInterface $user, $accessLevel)
    {
        $attTable = $this->getTableName('pim_catalog.entity.category.class');

        $qb = $this->getRevokedCategoryQB($user, $accessLevel);
        $qb
            ->select('DISTINCT a.id')
            ->innerJoin('c', $attTable, 'a', 'a.category_id = c.id');

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
        if (0 === count($categories)) {
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
        $groups = $qb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY);

        return $groups;
    }

    /**
     * Indicates whether a user is the owner of any categories
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isOwner(UserInterface $user)
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
     * Check if all categories are granted to user
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     * @param array         $categoryCodes
     *
     * @return bool
     */
    public function areAllCategoryCodesGranted(UserInterface $user, $accessLevel, array $categoryCodes)
    {
        $groupIds = array_map(
            function (GroupInterface $group) {
                return $group->getId();
            },
            $user->getGroups()->toArray()
        );

        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(DISTINCT c.id)')
            ->from($this->_entityName, 'ca');

        $qb
            ->innerJoin('ca.category', 'c', 'c.id')
            ->where($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $groupIds)
            ->andWhere($qb->expr()->eq('ca.' . $this->getAccessField($accessLevel), true))
            ->andWhere($qb->expr()->in('c.code', ':codes'))
            ->setParameter('codes', $categoryCodes);

        $numberOfGranted = $qb->getQuery()->getSingleScalarResult();

        return count($categoryCodes) === (int) $numberOfGranted;
    }

    /**
     * Check if categories are granted to user
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     * @param array         $categoryIds
     *
     * @return bool
     */
    public function isCategoriesGranted(UserInterface $user, $accessLevel, array $categoryIds)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from($this->_entityName, 'ca');

        $qb->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->andWhere($qb->expr()->in('c.id', ':categoryIds'))
            ->setParameter('categoryIds', $categoryIds)
            ->innerJoin('ca.category', 'c', 'c.id');

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getAccessField($accessLevel)
    {
        $mapping = [
            Attributes::OWN_PRODUCTS  => 'ownItems',
            Attributes::EDIT_ITEMS    => 'editItems',
            Attributes::VIEW_ITEMS    => 'viewItems',
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
     * @return int[]
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

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['category', 'user_group'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        list($categoryCode, $userGroupName) = explode('.', $identifier, 2);

        $associationMappings = $this->_em->getClassMetadata($this->_entityName)->getAssociationMappings();
        $categoryClass = $associationMappings['category']['targetEntity'];

        $qb = $this->createQueryBuilder('a')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->innerJoin($categoryClass, 'c', 'WITH', 'a.category = c.id')
            ->where('c.code = :categoryCode')
            ->andWhere('g.name = :userGroupName')
            ->setParameter('categoryCode', $categoryCode)
            ->setParameter('userGroupName', $userGroupName);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get granted children categories query builder
     *
     * @param CategoryInterface $category
     * @param UserInterface     $user
     * @param string            $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGrantedChildrenQB(CategoryInterface $category, UserInterface $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ca');
        $qb
            ->select('DISTINCT c.id')
            ->innerJoin('ca.category', 'c', 'c.id')
            ->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->lt('c.right', $category->getRight()),
                        $qb->expr()->gt('c.left', $category->getLeft()),
                        $qb->expr()->eq('c.root', $category->getRoot())
                    ),
                    $qb->expr()->eq('c.id', $category->getId())
                )
            );

        $qb->setParameter('groups', $user->getGroups()->toArray());

        return $qb;
    }

}
