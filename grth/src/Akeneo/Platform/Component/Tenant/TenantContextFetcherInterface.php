<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Tenant;

interface TenantContextFetcherInterface
{
    /**
     * Fetch the tenant context for a specific tenant ID
     * @retrun array<string, string>
     */
    public function getTenantContext(string $tenantId): array;
}
