<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessageWrapper
{
    private function __construct(
        private object $message,
        private string $tenantId,
        private string $correlationId,
    ) {
    }

    public static function create(object $message, string $tenantId): MessageWrapper
    {
        return new self($message, $tenantId, (Uuid::uuid4())->toString());
    }

    public static function fromNormalized(object $message, string $tenantId, string $correlationId): MessageWrapper
    {
        return new self($message, $tenantId, $correlationId);
    }

    public function message(): object
    {
        return $this->message;
    }

    public function tenantId(): string
    {
        return $this->tenantId;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }
}
