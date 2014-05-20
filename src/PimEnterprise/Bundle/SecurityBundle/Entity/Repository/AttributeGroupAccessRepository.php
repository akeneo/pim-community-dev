<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Oro\Bundle\UserBundle\Entity\User;

use Doctrine\ORM\EntityRepository;
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
     * Get authorized attribute group query builder
     *
     * @param User $user
     * @param string $accessLevel
     *
     * @return QueryBuilder
     */
    public function getAuthorizedAttributeGroupQB(User $user, $accessLevel)
    {
        $accessField = ($accessLevel === 'EDIT') ? 'aga.editAttributes' : 'aga.viewAttributes';

        $qb = $this->createQueryBuilder('aga');
        $qb
            ->andWhere($qb->expr()->in('aga.role', ':roles'))
            ->setParameter('roles', $user->getRoles())
            ->andWhere($qb->expr()->eq($accessField, true))
            ->resetDQLParts(['select'])
            ->innerJoin('aga.attributeGroup', 'ag', 'ag.id')
            ->select('ag.id');

        return $qb;
    }
}
