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
     * Gets root ACL type
     *
     * @return string
     */
    public function getRootType();

    /**
     * Checks if the given bitmask is valid for the given object.
     *
     * This method throws InvalidAclMaskException if the mask is invalid.
     *
     * @param string $permission
     * @param int $mask The bitmask
     * @param mixed $object An object to test
     * @throws InvalidAclMaskException
     */
    public function validateMask($permission, $mask, $object);

    /**
     * Constructs an ObjectIdentity for the given object
     *
     * @param mixed $object
     * @return ObjectIdentity
     */
    public function createObjectIdentity($object);

    /**
     * Gets the new instance of the mask builder which can be used to build permission bitmask
     * is supported by this ACL extension
     *
     * @param string $permission
     * @return MaskBuilder
     */
    public function createMaskBuilder($permission);

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
