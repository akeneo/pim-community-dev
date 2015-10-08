<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures;

use Oro\Bundle\SecurityBundle\Acl\Domain\PermissionGrantingStrategyContextInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionGrantingStrategyContext implements PermissionGrantingStrategyContextInterface
{
    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    private $object = null;

    private $token = null;

    public function __construct(AclExtensionSelector $selector)
    {
        $this->extensionSelector = $selector;
    }

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

    public function getAclExtension()
    {
        return $this->extensionSelector->select($this->object);
    }
}
