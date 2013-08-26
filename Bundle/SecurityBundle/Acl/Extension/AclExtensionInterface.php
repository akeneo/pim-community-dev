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
     * Checks if the given bitmask is valid for the given object.
     *
     * This method throws InvalidAclMaskException if the mask is invalid.
     *
     * @param int $mask The bitmask
     * @param mixed $object An object to test
     * @throws InvalidAclMaskException
     */
    public function validateMask($mask, $object);

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
     * @return MaskBuilder
     */
    public function createMaskBuilder();

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
     * Determines whether the access to the given domain object is granted
     * for an user is represented by the given security token.
     *
     * You can use this method to perform an additional check whether an access to the particular object is granted.
     * This method is called by the PermissionGrantingStrategy class after the suitable ACE found.
     *
     * @param int $aceMask The mask of triggered ACE
     * @param mixed $object
     * @param TokenInterface $securityToken
     * @return bool
     */
    public function decideIsGranting($aceMask, $object, TokenInterface $securityToken);
}
