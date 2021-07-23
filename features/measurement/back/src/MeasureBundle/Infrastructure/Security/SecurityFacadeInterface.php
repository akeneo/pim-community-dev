<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Infrastructure\Security;

/**
 * This interface should be in a shared dependency
 */
interface SecurityFacadeInterface
{
    public function isGranted(string $aclName): bool;
}
