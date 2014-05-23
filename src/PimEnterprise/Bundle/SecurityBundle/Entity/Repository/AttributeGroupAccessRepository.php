<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Attribute group access repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessRepository extends EntityRepository
{
    /**
     * Get roles that have the specified access to an attribute group
     *
     * @param AttributeGroup $group
     * @param string         $accessLevel
     *
     * @return Role[]
     */
    public function getGrantedRoles(AttributeGroup $group, $accessLevel)
    {
        $accessField = ($accessLevel === AttributeGroupVoter::EDIT_ATTRIBUTES) ? 'editAttributes' : 'viewAttributes';

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.attributeGroup = :group')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
            ->setParameter('group', $group);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to an attribute group
     * If excluded roles are provided, access will not be revoked for these roles
     *
     * @param AttributeGroup $group
     * @param Role[]         $excludedRoles
     *
     * @return integer
     */
    public function revokeAccess(AttributeGroup $group, array $excludedRoles = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.attributeGroup = :group')
            ->setParameter('group', $group);

        if (!empty($excludedRoles)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.role', ':excludedRoles'))
                ->setParameter('excludedRoles', $excludedRoles);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get granted attribute group query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedAttributeGroupQB(User $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('aga');
        $qb
            ->andWhere($qb->expr()->in('aga.role', ':roles'))
            ->setParameter('roles', $user->getRoles())
            ->andWhere($qb->expr()->eq($this->getAccessField($accessLevel), true))
            ->resetDQLParts(['select'])
            ->innerJoin('aga.attributeGroup', 'ag', 'ag.id')
            ->select('ag.id');

        return $qb;
    }

    /**
     * Get revoked attribute group query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getRevokedAttributeGroupQB(User $user, $accessLevel)
    {
        // prepare access field depending on access level
        $accessField = ($accessLevel === AttributeGroupVoter::EDIT_ATTRIBUTES)
            ? 'aga.edit_attributes'
            : 'aga.view_attributes';

        // get role ids
        $roleIds = array_map(
            function (RoleInterface $role) {
                return $role->getId();
            },
            $user->getRoles()
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
                        $qb->expr()->in('aga.role_id', $roleIds)
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
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getGrantedAttributeGroupIds(User $user, $accessLevel)
    {
        $qb = $this->getGrantedAttributeGroupQB($user, $accessLevel);

        return $this->hydrateAsIds($qb);
    }

    /**
     * Returns revoked attribute group ids
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedAttributeGroupIds(User $user, $accessLevel)
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
     * @param User   $user
     * @param string $accessLevel
     *
     * @return integer[]
     */
    public function getRevokedAttributeIds(User $user, $accessLevel)
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
     * @param User      $user
     * @param string    $accessLevel
     * @param integer[] $filterableIds
     *
     * @return integer[]
     */
    public function getGrantedAttributeIds(User $user, $accessLevel, array $filterableIds = null)
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
        return ($accessLevel === AttributeGroupVoter::EDIT_ATTRIBUTES)
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
