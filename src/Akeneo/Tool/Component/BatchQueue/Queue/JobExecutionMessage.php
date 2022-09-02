<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Object representing the message pushed into a queue to process a job execution asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class JobExecutionMessage implements JobExecutionMessageInterface
{
    private ?string $tenantId = null;

    private function __construct(
        private UuidInterface $id,
        private int $jobExecutionId,
        private \DateTime $createTime,
        private ?\DateTime $updatedTime,
        private array $options
    ) {
    }

    /**
     * Create a new JobExecutionMessage that has never been persisted into database.
     */
    public static function createJobExecutionMessage(
        int $jobExecutionId,
        array $options
    ): JobExecutionMessageInterface {
        $createTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return new static(Uuid::uuid4(), $jobExecutionId, $createTime, null, $options);
    }

    public static function createJobExecutionMessageFromNormalized(array $normalized): JobExecutionMessageInterface
    {
        return new static(
            Uuid::fromString($normalized['id']),
            $normalized['job_execution_id'],
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

    public function getJobExecutionId(): ?int
    {
        return $this->jobExecutionId;
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
