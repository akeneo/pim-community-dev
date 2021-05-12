<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Category access repository
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 *
 * TODO: It must be entity agnostic for PIM-4292.
 */
class CategoryAccessRepository extends EntityRepository implements IdentifiableObjectRepositoryInterface
{
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
            ->innerJoin(Group::class, 'g', 'WITH', 'a.userGroup = g.id')
            ->where('a.category = :category')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $this->getAccessField($accessLevel)), true))
            ->setParameter('category', $category);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findAccessesToRevoke(CategoryInterface $category, array $excludedGroups = []): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb
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
            ->where($qb->expr()->in('a.userGroup', ':groups'))
            ->setParameter('groups', $groups);

        $entities = $qb->getQuery()->execute();
        foreach ($entities as $categoryAccess) {
            $this->_em->remove($categoryAccess);
        }

        return count($entities);
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
            ->select('DISTINCT c.code');

        return array_map(
            function ($row) {
                return $row['code'];
            },
            $qb->getQuery()->getArrayResult()
        );
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
        $categoryAccessTable = $this->_class->table['name'];

        $pdo = $this->_em->getConnection()->getWrappedConnection();
        $stmt = $pdo->prepare(
            sprintf(
                "SELECT ca.category_id
                    FROM %s ca
                    JOIN oro_user_access_group ug
                        ON ug.group_id = ca.user_group_id
                    WHERE ug.user_id = :user_id
                      AND ca.%s = 1",
                $categoryAccessTable,
                $this->getAccessColumn($accessLevel)
            )
        );

        $userId = $user->getId();
        $stmt->bindParam('user_id', $userId);
        $stmt->execute();

        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 'ca.id');

        return array_map('intval', $ids);
    }

    /**
     * Get the granted user groups for a product
     *
     * @param CategoryAwareInterface $entity      the product
     * @param string                 $accessLevel the expected access level
     *
     * @return array
     */
    public function getGrantedUserGroupsForEntityWithValues(CategoryAwareInterface $entity, $accessLevel)
    {
        $categories = $entity->getCategories();
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
     * Check if at least one category (defined by theirs codes) is granted to user
     */
    public function isCategoryCodesGranted(UserInterface $user, string $accessLevel, array $categoryCodes): bool
    {
        $qb = $this->buildPartialQueryIsCategoryGranted($user, $accessLevel);
        $qb->andWhere($qb->expr()->in('c.code', ':categoryCodes'))
            ->setParameter('categoryCodes', $categoryCodes);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Check if at least one category (defined by theirs ids) is granted to user
     */
    public function isCategoryIdsGranted(UserInterface $user, string $accessLevel, array $categoryIds): bool
    {
        $qb = $this->buildPartialQueryIsCategoryGranted($user, $accessLevel);
        $qb->andWhere($qb->expr()->in('c.id', ':categoryIds'))
            ->setParameter('categoryIds', $categoryIds);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Build partial query for methods isCategoryCodesGranted() and isCategoryIdsGranted()
     */
    private function buildPartialQueryIsCategoryGranted(UserInterface $user, string $accessLevel): QueryBuilder
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from($this->_entityName, 'ca');

        return $qb->andWhere($qb->expr()->in('ca.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq('ca.'.$this->getAccessField($accessLevel), true))
            ->innerJoin('ca.category', 'c', 'c.id');
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
     * Get the column field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @throws \LogicException
     *
     * @return string
     */
    protected function getAccessColumn($accessLevel)
    {
        $mapping = [
            Attributes::OWN_PRODUCTS  => 'own_items',
            Attributes::EDIT_ITEMS    => 'edit_items',
            Attributes::VIEW_ITEMS    => 'view_items',
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
            ->innerJoin(Group::class, 'g', 'WITH', 'a.userGroup = g.id')
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
