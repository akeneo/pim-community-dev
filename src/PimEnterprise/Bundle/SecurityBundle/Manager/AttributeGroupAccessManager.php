<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Attribute group access manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccessManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $attributeGroupAccessClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $attributeGroupAccessClass
     */
    public function __construct(ManagerRegistry $registry, $attributeGroupAccessClass)
    {
        $this->registry                  = $registry;
        $this->attributeGroupAccessClass = $attributeGroupAccessClass;
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
        return $this->getRepository()->getGrantedRoles($group, AttributeGroupVoter::VIEW_ATTRIBUTES);
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
        return $this->getRepository()->getGrantedRoles($group, AttributeGroupVoter::EDIT_ATTRIBUTES);
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
        $grantedRoles = [];
        foreach ($editRoles as $role) {
            $this->grantAccess($group, $role, AttributeGroupVoter::EDIT_ATTRIBUTES);
            $grantedRoles[] = $role;
        }

        foreach ($viewRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($group, $role, AttributeGroupVoter::VIEW_ATTRIBUTES);
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($group, $grantedRoles);
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on an attribute group for the provided role
     *
     * @param AttributeGroup $group
     * @param Role           $role
     * @param string         $accessLevel
     */
    public function grantAccess(AttributeGroup $group, Role $role, $accessLevel)
    {
        $access = $this->getAttributeGroupAccess($group, $role);
        $access
            ->setViewAttributes(true)
            ->setEditAttributes($accessLevel === AttributeGroupVoter::EDIT_ATTRIBUTES);

        $this->getObjectManager()->persist($access);
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
            $access = new $this->attributeGroupAccessClass();
            $access
                ->setAttributeGroup($group)
                ->setRole($role);
        }

        return $access;
    }

    /**
     * Revoke access to an attribute group
     * If $excludedRoles are provided, access will not be revoked for roles with them
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
        return $this->registry->getRepository($this->attributeGroupAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->attributeGroupAccessClass);
    }
}
