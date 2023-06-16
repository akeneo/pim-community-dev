<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CorrelationIdStamp implements StampInterface
{
    public function __construct(private readonly string $correlationId)
    {
    }

    public static function generate(): self
    {
        return new self((Uuid::uuid4())->toString());
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }
}
