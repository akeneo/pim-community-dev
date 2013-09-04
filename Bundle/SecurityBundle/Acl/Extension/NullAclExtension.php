<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\SecurityBundle\Acl\Exception\InvalidAclMaskException;

/**
 * This class implements "Null object" design pattern for AclExtensionInterface
 */
final class NullAclExtension implements AclExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type, $id)
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getRootId()
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function validateMask($mask, $object, $permission = null)
    {
        throw new InvalidAclMaskException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($object)
    {
        throw new InvalidDomainObjectException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskBuilder($permission)
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMaskBuilders()
    {
        throw new \LogicException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaskPattern($mask)
    {
        return 'NullAclExtension: ' . $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMasks($permission)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRootAceMask($aceMask, $object)
    {
        return $aceMask;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceBits($mask)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function removeServiceBits($mask)
    {
        return $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessLevel($mask)
    {
        return AccessLevel::SYSTEM_LEVEL;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions($mask = null, $setOnly = false)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPermissions(ObjectIdentity $oid)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getClasses()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function decideIsGranting($triggeredMask, $object, TokenInterface $securityToken)
    {
        return true;
    }
}
