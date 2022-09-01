<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Object representing the message pushed into a queue to process a scheduled job.
 *
 * @author    JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SheduledJobMessage implements ScheduledJobMessageInterface
{
    private ?string $tenantId = null;

    private function __construct(
        private UuidInterface $id,
        private string $jobCode,
        private \DateTime $createTime,
        private ?\DateTime $updatedTime,
        private array $options
    ) {
    }

    public static function createScheduledJobMessage(
        string $jobCode,
        array $options
    ): ScheduledJobMessageInterface {
        $createTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return new static(Uuid::uuid4(), $jobCode, $createTime, null, $options);
    }

    public static function createFromNormalized(array $normalized): ScheduledJobMessageInterface
    {
        return new static(
            Uuid::fromString($normalized['id']),
            $normalized['job_code'],
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

    public function getJobCode(): string
    {
        return $this->jobCode;
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
