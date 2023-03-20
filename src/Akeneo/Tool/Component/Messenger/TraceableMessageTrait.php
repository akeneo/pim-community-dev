<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Messenger;

use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait TraceableMessageTrait
{
    private ?string $tenantId = null;
    private ?string $correlationId = null;

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getCorrelationId(): string
    {
        if (!$this->correlationId) {
            $this->correlationId = (Uuid::uuid4())->toString();
        }

        return $this->correlationId;
    }

    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }
}
