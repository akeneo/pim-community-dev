<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\Permission\InMemory;

use Oro\Bundle\SecurityBundle\SecurityFacade;

class SecurityFacadeStub extends SecurityFacade
{
    private array $permissions = [];

    public function isGranted($acl, $object = null): bool
    {
        return $this->permissions[$acl];
    }

    public function setIsGranted(string $acl, bool $isGranted)
    {
        $this->permissions[$acl] = $isGranted;
    }
}
