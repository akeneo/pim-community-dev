<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    JM leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ScheduledJobMessage implements JobExecutionMessageInterface
{
    private ?string $tenantId = null;

    private function __construct(
        private UuidInterface $id,
        private \DateTime $createTime,
        private ?\DateTime $updatedTime,
        private array $options
    ) {
    }

    public static function createJobExecutionMessageFromNormalized(array $normalized): JobExecutionMessageInterface
    {
        return new static(
            Uuid::fromString($normalized['id']),
            new \DateTime($normalized['created_time'] ?? 'now', new \DateTimeZone('UTC')),
            null !== $normalized['updated_time']
                ? new \DateTime($normalized['updated_time'], new \DateTimeZone('UTC'))
                : null,
            $normalized['options'] ?? []
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCreateTime(): \DateTime
    {
        return $this->createTime;
    }

    public function getUpdatedTime(): ?\DateTime
    {
        return $this->updatedTime;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
}
