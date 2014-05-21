<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

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
        $accessField = ($accessLevel === 'EDIT') ? 'a.editAttributes' : 'a.viewAttributes';

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.attributeGroup = :group')
            ->andWhere($qb->expr()->eq($accessField, true))
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
     * @return QueryBuilder
     */
    public function getGrantedAttributeGroupQB(User $user, $accessLevel)
    {
        $accessField = ($accessLevel === 'EDIT') ? 'aga.editAttributes' : 'aga.viewAttributes';

        $qb = $this->createQueryBuilder('aga');
        $qb
            ->resetDQLParts(['select'])
            ->select('ag.id')
            ->innerJoin('aga.attributeGroup', 'ag', 'ag.id')
            ->andWhere($qb->expr()->in('aga.role', ':roles'))
            ->andWhere($qb->expr()->eq($accessField, true))
            ->setParameter('roles', $user->getRoles());

        return $qb;
    }

    /**
     * Get revoked attribute group query builder
     *
     * @param User   $user
     * @param string $accessLevel
     *
     * @return QueryBuilder
     */
    public function getRevokedAttributeGroupQB(User $user, $accessLevel)
    {
        $accessField = ($accessLevel === 'EDIT') ? 'aga.editAttributes' : 'aga.viewAttributes';

        $qb = $this->createQueryBuilder('aga');
        $qb
            ->resetDQLParts(['select'])
            ->select('ag.id')
            ->leftJoin('aga.attributeGroup', 'ag', 'ag.id')
            ->andWhere($qb->expr()->in('aga.role', ':roles'))
            ->andWhere(
                $qb->expr()->neq($accessField, true)
            )
            ->setParameter('roles', $user->getRoles());

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

        return $this->hydrateAsIds($qb);
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
        $qb = $this->getRevokedAttributeGroupQB($user, $accessLevel);
        $qb
            ->select('a.id')
            ->innerJoin('ag.attributes', 'a');

        return $this->hydrateAsIds($qb);
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
        $results = $qb->getQuery()->getArrayResult();

        $resultIds = array();
        foreach ($results as $result) {
            $resultIds[] = $result['id'];
        }

        return $resultIds;
    }
}
