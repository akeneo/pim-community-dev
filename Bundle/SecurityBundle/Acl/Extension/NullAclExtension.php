<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * This class implements "Null object" design pattern for AclExtensionInterface
 */
final class NullAclExtension implements AclExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsObject($object)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidMask($mask, $object)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectIdentity($object)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createMaskBuilder()
    {
        return null;
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
