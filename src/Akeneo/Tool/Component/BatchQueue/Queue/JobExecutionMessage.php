<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

/**
 * Object representing the message pushed into a queue to process a job execution asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class JobExecutionMessage implements JobExecutionMessageInterface
{
    /** @var int */
    private $id;

    /** @var int */
    private $jobExecutionId;

    /** @var string */
    private $consumer;

    /** @var \DateTime */
    private $createTime;

    /** @var \DateTime */
    private $updatedTime;

    /** @var array */
    private $options = [];

    private function __construct(
        ?int $id,
        int $jobExecutionId,
        ?string $consumer,
        \DateTime $createTime,
        ?\DateTime $updatedTime,
        array $options
    ) {
        $this->id = $id;
        $this->jobExecutionId = $jobExecutionId;
        $this->consumer = $consumer;
        $this->createTime = $createTime;
        $this->updatedTime = $updatedTime;
        $this->options = $options;
    }

    /**
     * Create a new JobExecutionMessage that has never been persisted into database.
     */
    public static function createJobExecutionMessage(int $jobExecutionId, array $options): JobExecutionMessageInterface
    {
        $createTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return new static(null, $jobExecutionId, null, $createTime, null, $options);
    }

    /**
     * Create a JobExecutionMessage that has already been persisted in database.
     */
    public static function createJobExecutionMessageFromDatabase(
        int $id,
        int $jobExecutionId,
        ?string $consumer,
        \DateTime $createTime,
        ?\DateTime $updatedTime,
        array $options
    ): JobExecutionMessageInterface {
        return new static($id, $jobExecutionId, $consumer, $createTime, $updatedTime, $options);
    }

    public static function createJobExecutionMessageFromNormalized(array $normalized): JobExecutionMessageInterface
    {
        return new static(
            $normalized['id'], // @TODO CPM-156: replace id by a uuid? The id is only used for old queue.
            $normalized['job_execution_id'],
            $normalized['consumer'],
            new \DateTime($normalized['created_time']),
            null !== $normalized['updated_time'] ? new \DateTime($normalized['updated_time']) : null,
            $normalized['options']
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobExecutionId(): ?int
    {
        return $this->jobExecutionId;
    }

    public function getConsumer(): ?string
    {
        return $this->consumer;
    }

    public function consumedBy(string $consumer): void
    {
        $this->consumer = $consumer;
    }

    public function getCreateTime(): \DateTime
    {
        return $this->createTime;
    }

    /**
     * @return null|\DateTime
     */
    public function getUpdatedTime(): ?\DateTime
    {
        return $this->updatedTime;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
