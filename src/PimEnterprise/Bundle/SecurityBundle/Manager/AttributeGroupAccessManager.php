<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Attribute group access manager
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class AttributeGroupAccessManager
{
    /** @var AttributeGroupAccessRepository */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var string */
    protected $attGroupAccessClass;

    /**
     * @param AttributeGroupAccessRepository $repository
     * @param BulkSaverInterface             $saver
     * @param string                         $attGroupAccessClass
     */
    public function __construct(
        AttributeGroupAccessRepository $repository,
        BulkSaverInterface $saver,
        $attGroupAccessClass
    ) {
        $this->repository = $repository;
        $this->saver = $saver;
        $this->attGroupAccessClass = $attGroupAccessClass;
    }

    /**
     * Check if a user is granted to an attribute on a given permission
     *
     * @param UserInterface           $user
     * @param AttributeGroupInterface $group
     * @param string                  $permission
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isUserGranted(UserInterface $user, AttributeGroupInterface $group, $permission)
    {
        if (Attributes::EDIT_ATTRIBUTES === $permission) {
            $grantedUserGroups = $this->getEditUserGroups($group);
        } elseif (Attributes::VIEW_ATTRIBUTES === $permission) {
            $grantedUserGroups = $this->getViewUserGroups($group);
        } else {
            throw new \LogicException(sprintf('Attribute "%" is not supported.', $permission));
        }

        foreach ($grantedUserGroups as $userGroup) {
            if ($user->hasGroup($userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user groups that have view access to an attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return GroupInterface[]
     */
    public function getViewUserGroups(AttributeGroupInterface $group)
    {
        return $this->repository->getGrantedUserGroups($group, Attributes::VIEW_ATTRIBUTES);
    }

    /**
     * Get user groups that have edit access to an attribute group
     *
     * @param AttributeGroupInterface $group
     *
     * @return GroupInterface[]
     */
    public function getEditUserGroups(AttributeGroupInterface $group)
    {
        return $this->repository->getGrantedUserGroups($group, Attributes::EDIT_ATTRIBUTES);
    }

    /**
     * Grant access on an attribute group to specified user group
     *
     * @param AttributeGroupInterface $attributeGroup
     * @param GroupInterface[]        $viewUserGroups
     * @param GroupInterface[]        $editGroups
     */
    public function setAccess(AttributeGroupInterface $attributeGroup, $viewUserGroups, $editGroups)
    {
        $grantAccesses = [];
        $grantedUserGroups = [];
        foreach ($editGroups as $userGroup) {
            $grantAccesses[] = $this->buildGrantAccess($attributeGroup, $userGroup, Attributes::EDIT_ATTRIBUTES);
            $grantedUserGroups[] = $userGroup;
        }

        foreach ($viewUserGroups as $userGroup) {
            if (!in_array($userGroup, $grantedUserGroups)) {
                $grantAccesses[] = $this->buildGrantAccess($attributeGroup, $userGroup, Attributes::VIEW_ATTRIBUTES);
                $grantedUserGroups[] = $userGroup;
            }
        }

        $this->revokeAccess($attributeGroup, $grantedUserGroups);
        $this->saver->saveAll($grantAccesses);
    }

    /**
     * Grant specified access on an attribute group for the provided user group
     *
     * @param AttributeGroupInterface $attributeGroup
     * @param GroupInterface          $userGroup
     * @param string                  $accessLevel
     */
    public function grantAccess(AttributeGroupInterface $attributeGroup, GroupInterface $userGroup, $accessLevel)
    {
        $access = $this->buildGrantAccess($attributeGroup, $userGroup, $accessLevel);
        $this->saver->saveAll([$access]);
    }

    /**
     * Revoke access to an attribute group
     * If $excludedUserGroups are provided, access will not be revoked for groups with them
     *
     * @param AttributeGroupInterface $attributeGroup
     * @param GroupInterface[]        $excludedUserGroups
     *
     * @return int
     */
    public function revokeAccess(AttributeGroupInterface $attributeGroup, array $excludedUserGroups = [])
    {
        return $this->repository->revokeAccess($attributeGroup, $excludedUserGroups);
    }

    /**
     * Get AttributeGroupAccess entity for an attribute group and user group
     *
     * @param AttributeGroupInterface $attributeGroup
     * @param GroupInterface          $userGroup
     *
     * @return AttributeGroupAccess
     */
    protected function getAttributeGroupAccess(AttributeGroupInterface $attributeGroup, GroupInterface $userGroup)
    {
        $access = $this->repository
            ->findOneBy(
                [
                    'attributeGroup' => $attributeGroup,
                    'userGroup'      => $userGroup
                ]
            );

        if (!$access) {
            $access = new $this->attGroupAccessClass();
            $access
                ->setAttributeGroup($attributeGroup)
                ->setUserGroup($userGroup);
        }

        return $access;
    }

    /**
     * Build specified access on an attribute group for the provided user group
     *
     * @param AttributeGroupInterface $attributeGroup
     * @param GroupInterface          $userGroup
     * @param string                  $accessLevel
     *
     * @return AttributeGroupAccess
     */
    protected function buildGrantAccess(
        AttributeGroupInterface $attributeGroup,
        GroupInterface $userGroup,
        $accessLevel
    ) {
        $access = $this->getAttributeGroupAccess($attributeGroup, $userGroup);
        $access
            ->setViewAttributes(true)
            ->setEditAttributes($accessLevel === Attributes::EDIT_ATTRIBUTES);

        return $access;
    }
}
