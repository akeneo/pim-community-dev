<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategyContext as ContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionGrantingStrategyContext implements ContextInterface
{
    private $object = null;

    private $token = null;

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getSecurityToken()
    {
        return $this->token;
    }

    public function setSecurityToken(TokenInterface $token)
    {
        $this->token = $token;
    }
}
