<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Security;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * We need to create this class as there is no interface for the service "SecurityFacade" in the shared|tool component
 */
class SecurityFacadeProxy implements SecurityFacadeInterface
{
    private SecurityFacade $externalService;

    public function __construct(SecurityFacade $externalService)
    {
        $this->externalService = $externalService;
    }

    public function isGranted(string $aclName): bool
    {
        return $this->externalService->isGranted($aclName);
    }
}
