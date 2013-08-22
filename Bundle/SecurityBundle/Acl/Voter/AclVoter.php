<?php

namespace Oro\Bundle\SecurityBundle\Acl\Voter;

use Symfony\Component\Security\Acl\Voter\AclVoter as BaseAclVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategyContext;

/**
 * This voter uses ACL to determine whether the access to the particular resource is granted or not.
 */
class AclVoter extends BaseAclVoter implements PermissionGrantingStrategyContext
{
    /**
     * An object which is the subject of the current voting operation
     *
     * @var mixed
     */
    private $object = null;

    /**
     * The security token of the current voting operation
     *
     * @var mixed
     */
    private $securityToken = null;

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $this->securityToken = $token;
        $this->object = $object;
        $result = parent::vote($token, $object, $attributes);
        $this->object = null;
        $this->securityToken = null;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }
}
