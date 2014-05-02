<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Oro\Bundle\UserBundle\Entity\Role;
use PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess;

/**
 * Attribute group access manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

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
        $qb = $this->objectManager
            ->getRepository('PimEnterpriseSecurityBundle:AttributeGroupAccess')
            ->createQueryBuilder('a');

        $accessField = $accessLevel === 'EDIT' ? 'a.editAttributes' : 'a.viewAttributes';

        $qb->select('r')
            ->leftJoin('OroUserBundle:Role', 'r', 'WITH', 'a.roleId = r.id')
            ->where($qb->expr()->eq('a.attributeGroupId', $group->getId()))
            ->andWhere($qb->expr()->eq($accessField, true));

        return $qb->getQuery()->getResult();
    }

    /**
     * Grant access on an attribute group to specified roles
     *
     * @param AttributeGroup $group
     * @param Role[]         $viewRoles
     * @param Role[]         $editRoles
     */
    public function setAccess(AttributeGroup $group, $viewRoles, $editRoles)
    {
        $grantedRoleIds = [];
        foreach ($viewRoles as $role) {
            $this->grantAccess($group, $role, 'VIEW');
            $grantedRoleIds[] = $role->getId();
        }

        foreach ($editRoles as $role) {
            $this->grantAccess($group, $role, 'EDIT');
            $grantedRoleIds[] = $role->getId();
        }

        $this->revokeAccess($group, array_unique($grantedRoleIds));
        $this->objectManager->flush();
    }

    /**
     * Grant specified access on an attribute group for the provided role
     *
     * @param AttributeGroup $group
     * @param Role           $role
     * @param string         $accessLevel
     */
    protected function grantAccess(AttributeGroup $group, Role $role, $accessLevel)
    {
        $access = $this->getAttributeGroupAccess($group, $role);

        $access
            ->setViewAttributes(true)
            ->setEditAttributes($accessLevel === 'EDIT');

        $this->objectManager->persist($access);
    }

    /**
     * Get AttributeGroupAccess entity for a group and role
     *
     * @param AttributeGroup $group
     * @param Role           $role
     *
     * @return AttributeGroupAccess
     */
    protected function getAttributeGroupAccess(AttributeGroup $group, Role $role)
    {
        $access = $this->objectManager
            ->getRepository('PimEnterpriseSecurityBundle:AttributeGroupAccess')
            ->findOneBy(
                [
                    'attributeGroupId' => $group->getId(),
                    'roleId'           => $role->getId()
                ]
            );

        if (!$access) {
            $access = new AttributeGroupAccess();
            $access
                ->setAttributeGroupId($group->getId())
                ->setRoleId($role->getId());
        }

        return $access;
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
    protected function revokeAccess(AttributeGroup $group, array $excludedIds = [])
    {
        $qb = $this->objectManager
            ->getRepository('PimEnterpriseSecurityBundle:AttributeGroupAccess')
            ->createQueryBuilder('a');

        $qb->delete()
            ->where($qb->expr()->eq('a.attributeGroupId', $group->getId()));

        if (!empty($excludedIds)) {
            $qb->andWhere($qb->expr()->notIn('a.roleId', $excludedIds));
        }

        return $qb->getQuery()->execute();
    }
}
