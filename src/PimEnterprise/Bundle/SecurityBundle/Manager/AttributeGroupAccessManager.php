<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
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
     * Get roles that have view access to an attribute group
     *
     * @param AttributeGroup $group
     *
     * @return Role[]
     */
    public function getViewRoles(AttributeGroup $group)
    {
        return $this->getRepository()->getGrantedRoles($group, 'VIEW');
    }

    /**
     * Get roles that have edit access to an attribute group
     *
     * @param AttributeGroup $group
     *
     * @return Role[]
     */
    public function getEditRoles(AttributeGroup $group)
    {
        return $this->getRepository()->getGrantedRoles($group, 'EDIT');
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
        $grantedRoles = array();
        foreach ($editRoles as $role) {
            $this->grantAccess($group, $role, 'EDIT');
            $grantedRoles[] = $role;
        }

        foreach ($viewRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($group, $role, 'VIEW');
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($group, array_unique($grantedRoles));
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
        $access = $this->getRepository()
            ->findOneBy(
                [
                    'attributeGroup' => $group,
                    'role'           => $role
                ]
            );

        if (!$access) {
            $access = new AttributeGroupAccess();
            $access
                ->setAttributeGroup($group)
                ->setRole($role);
        }

        return $access;
    }

    /**
     * Revoke access to an attribute group
     * If excludedIds are provided, access will not be revoked for roles with these ids
     *
     * @param AttributeGroup $group
     * @param Role[]         $excludedRoles
     *
     * @return integer
     */
    protected function revokeAccess(AttributeGroup $group, array $excludedRoles = [])
    {
        return $this->getRepository()->revokeAccess($group, $excludedRoles);
    }

    /**
     * Get repository
     *
     * @return AttributeGroupAccessRepository
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository('PimEnterpriseSecurityBundle:AttributeGroupAccess');
    }
}
