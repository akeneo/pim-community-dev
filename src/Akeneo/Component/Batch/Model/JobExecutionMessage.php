<?php

namespace Akeneo\Component\Batch\Model;

/**
 * Object representing the message pushed into a queue to process a job execution asynchronously.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionMessage
{
    /** @var integer */
    private $id;

    /** @var JobExecution */
    private $jobExecution;

    /** @var string */
    private $consumer;

    /** @var \DateTime */
    private $createTime;

    /** @var \DateTime */
    private $updatedTime;

    /** @var string */
    private $commandName;

    /** @var array */
    private $options = [];

    /**
     * @param JobExecution $jobExecution
     * @param string       $commandName
     * @param array        $options
     */
    public function __construct(JobExecution $jobExecution, string $commandName, array $options)
    {
        $this->jobExecution = $jobExecution;
        $this->commandName = $commandName;
        $this->options = $options;
        $this->createTime = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return JobExecution
     */
    public function getJobExecution(): JobExecution
    {
        return $this->jobExecution;
    }

    /**
     * @return string
     */
    public function getConsumer(): string
    {
        return $this->consumer;
    }

    /**
     * @param string $consumer
     *
     * @return JobExecutionMessage
     */
    public function setConsumer(string $consumer)
    {
        $this->consumer = $consumer;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime(): \DateTime
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime $createTime
     *
     * @return JobExecutionMessage
     */
    public function setCreateTime(\DateTime $createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedTime(): \DateTime
    {
        return $this->updatedTime;
    }

    /**
     * @param \DateTime $updatedTime
     *
     * @return JobExecutionMessage
     */
    public function setUpdatedTime(\DateTime $updatedTime)
    {
        $this->updatedTime = $updatedTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
