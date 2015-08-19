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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Attribute group access repository
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class AttributeGroupAccessRepository extends EntityRepository
{
    /**
     * @var TableNameBuilder
     */
    protected $tableNameBuilder;

    /**
     * Get user groups that have the specified access to an attribute group
     *
     * @param AttributeGroupInterface $group
     * @param string                  $accessLevel
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group[]
     */
    public function getGrantedUserGroups(AttributeGroupInterface $group, $accessLevel)
    {
        $accessField = ($accessLevel === Attributes::EDIT_ATTRIBUTES) ? 'editAttributes' : 'viewAttributes';

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('g')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->where('a.attributeGroup = :group')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
            ->setParameter('group', $group);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to an attribute group
     * If excluded user groups are provided, access will not be revoked for these groups
     *
     * @param AttributeGroupInterface               $group
     * @param \Oro\Bundle\UserBundle\Entity\Group[] $excludedUserGroups
     *
     * @return int
     */
    public function revokeAccess(AttributeGroupInterface $group, array $excludedUserGroups = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.attributeGroup = :group')
            ->setParameter('group', $group);

        if (!empty($excludedUserGroups)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.userGroup', ':excludedUserGroups'))
                ->setParameter('excludedUserGroups', $excludedUserGroups);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get granted attribute group query builder
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedAttributeGroupQB(UserInterface $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('aga');
        $qb
            ->andWhere($qb->expr()->in('aga.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq($this->getAccessField($accessLevel), true))
            ->resetDQLParts(['select'])
            ->innerJoin('aga.attributeGroup', 'ag', 'ag.id')
            ->select('ag.id');

        return $qb;
    }

    /**
     * Get revoked attribute group query builder
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getRevokedAttributeGroupQB(UserInterface $user, $accessLevel)
    {
        // prepare access field depending on access level
        $accessField = ($accessLevel === Attributes::EDIT_ATTRIBUTES)
            ? 'aga.edit_attributes'
            : 'aga.view_attributes';

        // get group ids
        $groupIds = array_map(
            function (Group $group) {
                return $group->getId();
            },
            $user->getGroups()->toArray()
        );

        $groupTable = $this->getTableName('pim_catalog.entity.attribute_group.class');
        $groupAccessTable = $this->getTableName('pimee_security.entity.attribute_group_access.class');

        $conn = $this->_em->getConnection();
        $qb = $conn->createQueryBuilder();
        $qb
            ->select('*')
            ->from($groupTable, 'g')
            ->leftJoin('g', $groupAccessTable, 'aga', 'aga.attribute_group_id = g.id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->neq($accessField, true),
                        $qb->expr()->in('aga.user_group_id', $groupIds)
                    ),
                    $qb->expr()->isNull($accessField)
                )
            )
            ->groupBy('g.id');

        return $qb;
    }

    /**
     * Returns granted attribute groups ids
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return integer[]
     */
    public function getGrantedAttributeGroupIds(UserInterface $user, $accessLevel)
    {
        $qb = $this->getGrantedAttributeGroupQB($user, $accessLevel);

        return $this->hydrateAsIds($qb);
    }

    /**
     * Returns revoked attribute group ids
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedAttributeGroupIds(UserInterface $user, $accessLevel)
    {
        $qb = $this->getRevokedAttributeGroupQB($user, $accessLevel);
        $qb->select('g.id');

        return array_map(
            function ($row) {
                return $row['id'];
            },
            $qb->execute()->fetchAll()
        );
    }

    /**
     * Returns revoked attribute ids
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedAttributeIds(UserInterface $user, $accessLevel)
    {
        $attTable = $this->getTableName('pim_catalog.entity.attribute.class');

        $qb = $this->getRevokedAttributeGroupQB($user, $accessLevel);
        $qb
            ->select('a.id')
            ->innerJoin('g', $attTable, 'a', 'a.group_id = g.id')
            ->groupBy('a.id');

        return array_map(
            function ($row) {
                return $row['id'];
            },
            $qb->execute()->fetchAll()
        );
    }

    /**
     * Get granted attribute ids for a user
     * If $filterableIds is provided, the returned ids will consist of these ids
     * filtered by the given access level
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     * @param integer[]     $filterableIds
     *
     * @return integer[]
     */
    public function getGrantedAttributeIds(UserInterface $user, $accessLevel, array $filterableIds = null)
    {
        $qb = $this->getGrantedAttributeGroupQB($user, $accessLevel);
        $qb
            ->select('a.id')
            ->innerJoin('ag.attributes', 'a')
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
        return ($accessLevel === Attributes::EDIT_ATTRIBUTES)
            ? 'aga.editAttributes'
            : 'aga.viewAttributes';
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
     * @return AttributeGroupAccessRepository
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
