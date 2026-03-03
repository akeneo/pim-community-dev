<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;

class InMemorySecurityFacade implements SecurityFacadeInterface
{
    private array $permissions = [];

    public function isGranted($acl, $object = null): bool
    {
        return $this->permissions[$acl];
    }

    public function setIsGranted(string $acl, bool $isGranted): void
    {
        $this->permissions[$acl] = $isGranted;
    }
}
