<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;

interface AclExtensionInterface
{
    /**
     * Checks if the ACL extension supports the given object.
     *
     * @param string $type A type of an object to test
     * @param mixed $id An id of an object to test
     * @return bool true if this ACL extension can process the object
     */
    public function supports($type, $id);

    /**
     * Gets root ACL identifier.
     * All characters in the identifier must be lowercase.
     *
     * @return string
     */
    public function getRootId();

    /**
     * Checks if the given bitmask is valid for the given object.
     *
     * This method throws InvalidAclMaskException if the mask is invalid.
     *
     * @param int $mask The bitmask
     * @param mixed $object An object to test
     * @param string|null $permission If null checks all permissions; otherwise, check only the given permission
     * @throws InvalidAclMaskException
     */
    public function validateMask($mask, $object, $permission = null);

    /**
     * Constructs an ObjectIdentity for the given object
     *
     * @param mixed $object
     * @return ObjectIdentity
     */
    public function getObjectIdentity($object);

    /**
     * Gets the new instance of the mask builder which can be used to build permission bitmask
     * supported this ACL extension
     *
     * @param string $permission
     * @return MaskBuilder
     */
    public function getMaskBuilder($permission);

    /**
     * Gets all mask builders supported this ACL extension
     *
     * @return MaskBuilder[]
     */
    public function getAllMaskBuilders();

    /**
     * Gets a human-readable representation of the given mask
     *
     * @param int $mask
     * @return string
     */
    public function getMaskPattern($mask);

    /**
     * Returns an array of bitmasks for the given permission.
     *
     * The security identity must have been granted access to at least one of these bitmasks.
     *
     * @param string $permission
     * @return array may return null if permission/object combination is not supported
     */
    public function getMasks($permission);

    /**
     * Determines whether the ACL extension contains the given permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasMasks($permission);

    /**
     * Check the given ACE mask of the root ACL and remove redundant for the given object bits from it
     *
     * @param int $aceMask
     * @param mixed $object
     * @return int The ACE mask without redundant bits
     */
    public function prepareRootAceMask($aceMask, $object);

    /**
     * Remove all bits except service ones from the given mask
     *
     * @param int $mask
     * @return int The mask without service bits
     */
    public function getServiceBits($mask);

    /**
     * Remove service bits from the given mask
     *
     * @param int $mask
     * @return int The mask without service bits
     */
    public function removeServiceBits($mask);

    /**
     * Gets the access level by the given mask
     *
     * @param int $mask
     * @return int Can be one of AccessLevel::*_LEVEL constants
     */
    public function getAccessLevel($mask);

    /**
     * Gets all permissions supported by this ACL extension
     * or permissions encoded in the given mask if $mask argument is not null.
     * Also you can use $setOnly argument to specify whether you need a list of
     * all permissions which can be encoded in the given mask or only a list of
     * permissions are set in the given mask.
     *
     * @param int|null $mask The bitmask
     * @param bool $setOnly Determines whether all permissions can be encoded in the given mask should be returned
     *                      or only permissions are set in the given mask.
     * @return string[]
     */
    public function getPermissions($mask = null, $setOnly = false);

    /**
     * Gets all permissions allowed for a domain object represented by te given object identity.
     *
     * @param ObjectIdentity $oid
     * @return string[]
     */
    public function getAllowedPermissions(ObjectIdentity $oid);

    /**
     * Gets all types of domain objects or resources supported by this ACL extension.
     *
     * @return string[]
     */
    public function getClasses();

    /**
     * Determines whether the access to the given domain object is granted
     * for an user is represented by the given security token.
     *
     * You can use this method to perform an additional check whether an access to the particular object is granted.
     * This method is called by the PermissionGrantingStrategy class after the suitable ACE found.
     *
     * @param int $triggeredMask The triggered mask
     * @param mixed $object
     * @param TokenInterface $securityToken
     * @return bool
     */
    public function decideIsGranting($triggeredMask, $object, TokenInterface $securityToken);
}
