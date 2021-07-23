<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Structure;

use AkeneoMeasureBundle\Infrastructure\Security\SecurityFacadeInterface;

class InMemorySecurityFacade implements SecurityFacadeInterface
{
    public function isGranted(string $aclName): bool
    {
        return true;
    }
}
