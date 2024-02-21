<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A contract all ACL extensions must implement.
 */
interface AclExtensionInterface
{
    /**
     * Checks if the ACL extension supports an object of the given type and with the given id.
     *
     * @param string $type A type of an object to test
     * @param mixed $id An id of an object to test
     * @return bool true if this class is valid ACL extension for the given object; otherwise, false
     */
    public function supports($type, $id);

    /**
     * Gets a string which is identifies this ACL extension.
     * The key must correspond the following criteria:
     *     - each ACL extension must have an unique key
     *     - all characters in the key must be lowercase.
     *
     * @return string
     */
    public function getExtensionKey();

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
     * @param mixed $val A domain object, object identity descriptor (id:type) or ACL annotation
     * @return ObjectIdentity
     */
    public function getObjectIdentity($val);

    /**
     * Gets the new instance of the mask builder which can be used to build permission bitmask
     * supported this ACL extension.
     *
     * As one ACL extension may support several bitmasks (and as result it gives us an ability to
     * associate several ACEs with the same domain object) we need should use correct implementation
     * of the mask builder for each type of a bitmask. To find correct mask builder we can use one of
     * a permission name the required mask builder supports.
     *
     * @param string $permission
     * @return MaskBuilder
     */
    public function getMaskBuilder($permission);

    /**
     * Gets all mask builders supported this ACL extension
     *
     * As one ACL extension may support several bitmasks (and as result it gives us an ability to
     * associate several ACEs with the same domain object) we need a separate implementation of the builder
     * for each type of a bitmask.
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
     * Adapts the given mask to use with the given object.
     * As the root mask is more general and cannot take in account a specific of each domain object
     * it should be adapted before it can be applied to a particular domain object.
     *
     * @param int $rootMask
     * @param mixed $object
     * @return int The ACE mask without redundant bits
     */
    public function adaptRootMask($rootMask, $object);

    /**
     * Remove all bits except service ones from the given mask
     *
     * As one ACL extension may support several bitmasks (and as result it gives us an ability to
     * associate several ACEs with the same domain object) we need a way to differentiate them.
     * It is an aim of service bits in a bitmask.
     *
     * @param int $mask
     * @return int The mask without service bits
     */
    public function getServiceBits($mask);

    /**
     * Remove service bits from the given mask
     *
     * As one ACL extension may support several bitmasks (and as result it gives us an ability to
     * associate several ACEs with the same domain object) we need a way to differentiate them.
     * It is an aim of service bits in a bitmask.
     *
     * @param int $mask
     * @return int The mask without service bits
     */
    public function removeServiceBits($mask);

    /**
     * Gets the access level for one permission encoded in the given mask.
     * If $permission argument is null the $mask argument must contains a bitmask for one and only one permission only;
     * otherwise, the result of this method is not predicted.
     * If $mask argument contains a bitmask for several permissions you must specify a permission
     * for which the access level you need to check.
     *
     * @param int $mask
     * @param string $permission
     * @return int Can be one of AccessLevel::*_LEVEL constants
     */
    public function getAccessLevel($mask, $permission = null);

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
     * Gets default permission.
     * If ACL extension supports only one permission then this permission is default one.
     * If ACL extension supports several permissions and there is no default permission
     * this method must return empty string.
     *
     * @return string
     */
    public function getDefaultPermission();

    /**
     * Gets all types of domain objects or resources supported by this ACL extension.
     *
     * @return AclClassInfo[]
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
