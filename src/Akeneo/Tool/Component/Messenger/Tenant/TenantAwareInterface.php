<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger\Tenant;

interface TenantAwareInterface
{
    public function getTenantId(): ?string;
    public function setTenantId(string $tenantId): void;
}
