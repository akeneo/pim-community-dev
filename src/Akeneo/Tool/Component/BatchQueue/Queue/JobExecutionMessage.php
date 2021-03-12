<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Webmozart\Assert\Assert;

/**
 * @TODO: CPM-156: make it abstract
 *
 * Object representing the message pushed into a queue to process a job execution asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionMessage implements JobExecutionMessageInterface
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

    /**
     * @param int       $id
     * @param int       $jobExecutionId
     * @param string    $consumer
     * @param \DateTime $createTime
     * @param \DateTime $updatedTime
     * @param array     $options
     */
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
     *
     * @param int   $jobExecutionId
     * @param array $options
     *
     * @return JobExecutionMessage
     */
    public static function createJobExecutionMessage(int $jobExecutionId, array $options): JobExecutionMessage
    {
        $createTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return new static(null, $jobExecutionId, null, $createTime, null, $options);
    }

    /**
     * Create a JobExecutionMessage that has already been persisted in database.
     *
     * @param int            $id
     * @param int            $jobExecutionId
     * @param null|string    $consumer
     * @param \DateTime      $createTime
     * @param \DateTime|null $updatedTime
     * @param array          $options
     *
     * @return JobExecutionMessage
     */
    public static function createJobExecutionMessageFromDatabase(
        int $id,
        int $jobExecutionId,
        ?string $consumer,
        \DateTime $createTime,
        ?\DateTime $updatedTime,
        array $options
    ): JobExecutionMessage {
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

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getJobExecutionId(): ?int
    {
        return $this->jobExecutionId;
    }

    /**
     * @return null|string
     */
    public function getConsumer(): ?string
    {
        return $this->consumer;
    }

    /**
     * @param string $consumer
     *
     * @return JobExecutionMessage
     */
    public function consumedBy(string $consumer): void
    {
        $this->consumer = $consumer;
    }

    /**
     * @return \DateTime
     */
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
