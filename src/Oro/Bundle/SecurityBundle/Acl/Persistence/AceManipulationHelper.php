<?php

namespace Oro\Bundle\SecurityBundle\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface as ACL;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;

class AceManipulationHelper
{
    /**
     * Updates or creates ACE with the given attributes for the given ACL
     *
     * @param ACL $acl
     * @param AclExtensionInterface $extension
     * @param bool $replace If true the mask and strategy of the existing ACE should be replaced with the given ones
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID $sid
     * @param bool $granting
     * @param int $mask
     * @param string|null $strategy If null the strategy should not be changed for existing ACE
     *                              or the appropriate strategy should be  selected automatically for new ACE
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     * @return bool True if a permission was updated or created
     */
    public function setPermission(
        ACL $acl,
        AclExtensionInterface $extension,
        $replace,
        $type,
        $field,
        SID $sid,
        $granting,
        $mask,
        $strategy = null
    ) {
        $hasChanges = false;
        $found = false;
        $maskServiceBits = $extension->getServiceBits($mask);
        $aces = $this->getAces($acl, $type, $field);
        foreach ($aces as $index => $ace) {
            if ($sid->equals($ace->getSecurityIdentity()) && $granting === $ace->isGranting()) {
                if ($mask === $ace->getMask() && ($strategy === null || $strategy === $ace->getStrategy())) {
                    $found = true;
                } elseif ($replace && $maskServiceBits === $extension->getServiceBits($ace->getMask())) {
                    $this->updateAce($acl, $type, $field, $index, $mask, $strategy);
                    $found = true;
                    $hasChanges = true;
                }
            }
        }
        if (!$found) {
            $this->insertAce($acl, $type, $field, 0, $sid, $granting, $mask, $strategy);
            $hasChanges = true;
        }

        return $hasChanges;
    }

    /**
     * Deletes ACE with the given attributes from the given ACL
     *
     * @param ACL $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID $sid
     * @param bool $granting
     * @param int $mask
     * @param string|null $strategy If null ACE with any strategy should be deleted
     * @return bool True if a permission was deleted
     */
    public function deletePermission(
        ACL $acl,
        $type,
        $field,
        SID $sid,
        $granting,
        $mask,
        $strategy = null
    ) {
        $hasChanges = false;
        $aces = $this->getAces($acl, $type, $field);
        foreach ($aces as $index => $ace) {
            if ($sid->equals($ace->getSecurityIdentity()) && $granting === $ace->isGranting()
                && $mask === $ace->getMask() && ($strategy === null || $strategy === $ace->getStrategy())
            ) {
                $this->deleteAce($acl, $type, $field, $index);
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }

    /**
     * Deletes all ACEs for the given security identity from the given ACL
     *
     * @param ACL $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param SID $sid
     * @return bool True if at least one permission was deleted
     */
    public function deleteAllPermissions(ACL $acl, $type, $field, SID $sid)
    {
        $hasChanges = false;
        $aces = $this->getAces($acl, $type, $field);
        foreach ($aces as $index => $ace) {
            if ($sid->equals($ace->getSecurityIdentity())) {
                $this->deleteAce($acl, $type, $field, $index);
                $hasChanges = true;
            }
        }

        return $hasChanges;
    }

    /**
     * Gets all ACEs associated with the given ACL
     *
     * @param AclInterface $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @return EntryInterface[]
     */
    public function getAces(AclInterface $acl, $type, $field)
    {
        if ($field === null) {
            return $acl->{"get{$type}Aces"}();
        } else {
            return $acl->{"get{$type}FieldAces"}($field);
        }
    }

    /**
     * Inserts an ACE into the given ACL
     *
     * @param ACL $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param integer $index
     * @param SID $sid
     * @param bool $granting
     * @param int $mask
     * @param string|null $strategy If null the appropriate strategy should be selected automatically
     *                                  ALL strategy is used for $granting = true
     *                                  ANY strategy is used for $granting = false
     */
    public function insertAce(ACL $acl, $type, $field, $index, SID $sid, $granting, $mask, $strategy = null)
    {
        if ($field === null) {
            $acl->{"insert{$type}Ace"}($sid, $mask, $index, $granting, $strategy);
        } else {
            $acl->{"insert{$type}FieldAce"}($field, $sid, $mask, $index, $granting, $strategy);
        }
    }

    /**
     * Updates an ACE with the given index in the given ACL
     *
     * @param ACL $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param integer $index
     * @param integer $mask
     * @param string $strategy If null the strategy should not be changed
     */
    public function updateAce(ACL $acl, $type, $field, $index, $mask, $strategy = null)
    {
        if ($field === null) {
            $acl->{"update{$type}Ace"}($index, $mask, $strategy);
        } else {
            $acl->{"update{$type}FieldAce"}($index, $field, $mask, $strategy);
        }
    }

    /**
     * Deletes an ACE with the given index from the given ACL
     *
     * @param ACL $acl
     * @param string $type The ACE type. Can be one of AclManager::*_ACE constants
     * @param string|null $field The name of a field.
     *                           Set to null for class-based or object-based ACE
     *                           Set to not null class-field-based or object-field-based ACE
     * @param integer $index
     */
    public function deleteAce(ACL $acl, $type, $field, $index)
    {
        if ($field === null) {
            $acl->{"delete{$type}Ace"}($index);
        } else {
            $acl->{"delete{$type}FieldAce"}($index, $field);
        }
    }
}
