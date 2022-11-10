<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Tenant;

interface TenantContextFetcherInterface
{
    /**
     * Fetch the tenant context for a specific tenant ID
     * @retrun array<string, string>
     */
    public function getTenantContext(string $tenantId): array;
}
