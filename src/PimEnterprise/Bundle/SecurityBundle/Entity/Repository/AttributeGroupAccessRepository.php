<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

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
        $qb = $this->createQueryBuilder('a');

        $accessField = $accessLevel === 'EDIT' ? 'a.editAttributes' : 'a.viewAttributes';

        $qb->select('r')
            ->leftJoin('OroUserBundle:Role', 'r', 'WITH', 'a.roleId = r.id')
            ->where($qb->expr()->eq('a.attributeGroupId', $group->getId()))
            ->andWhere($qb->expr()->eq($accessField, true));

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to an attribute group
     * If excludedIds are provided, access will not be revoked for roles with these ids
     *
     * @param AttributeGroup $group
     * @param integer[]      $excludedIds
     *
     * @return integer
     */
    public function revokeAccess(AttributeGroup $group, array $excludedIds = [])
    {
        $qb = $this->createQueryBuilder('a');

        $qb->delete()
            ->where($qb->expr()->eq('a.attributeGroupId', $group->getId()));

        if (!empty($excludedIds)) {
            $qb->andWhere($qb->expr()->notIn('a.roleId', $excludedIds));
        }

        return $qb->getQuery()->execute();
    }
}
