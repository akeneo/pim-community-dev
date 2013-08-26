<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

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
    public function validateMask($mask, $object)
    {
        throw new InvalidAclMaskException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        throw new InvalidDomainObjectException('Not supported by NullAclExtension.');
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder()
    {
        throw new \LogicException('Not supported by NullAclExtension.');
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
    public function decideIsGranting($aceMask, $object, TokenInterface $securityToken)
    {
        return true;
    }
}
