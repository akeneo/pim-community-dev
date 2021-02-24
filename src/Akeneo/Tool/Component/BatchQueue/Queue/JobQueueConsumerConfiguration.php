<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;

/**
 * Configuration for Job Queue Consumer
 *
 *
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobQueueConsumerConfiguration implements \ArrayAccess
{
    /**
     * Job code to execute by the consumer.
     * If set, all jobs will NOT be executed but the ones given in the whitelist.
     *
     * @var array
     * */
    private $whitelistedJobInstanceCodes = [];

    /**
     * Job codes NOT to execute by the consumer.
     * If this is set, all jobs will be executed but the ones given in the blacklist.
     * When both lists are empty, all jobs consumed are executed.
     *
     * @var array
     * */
    private $blacklistedJobInstanceCodes = [];

    /**
     * Number of seconds to wait between each check when polling jobs queue.
     *
     * @var int
     * */
    private $queueCheckInterval = 5;

    /**
     * Number of job iterations before the consumer waits for a job.
     * 0 means the process does wait for a job forever.
     *
     * @var int
     * */
    private $timeToLive = 0;

    /** @var array */
    private $listSupportedSettings = ["whitelistedJobInstanceCodes", "blacklistedJobInstanceCodes", "queueCheckInterval", "timeToLive"];
    

    public function setWhitelistedJobInstanceCodes(array $codes): Self
    {
        if (0 === count($codes)) {
            return $this;
        }

        if (count($this->blacklistedJobInstanceCodes) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Cannot set a job queue whitelist '{%s}' in consumer since a blacklist '{%s}' is already defined.",
                    join(', ', $codes),
                    join(', ', $this->blacklistedJobInstanceCodes)
                )
            );
        }

        $this->whitelistedJobInstanceCodes = $codes;

        return $this;
    }

    public function setBlacklistedJobInstanceCodes(array $codes): Self
    {
        if (0 === count($codes)) {
            return $this;
        }

        if (count($this->whitelistedJobInstanceCodes) > 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Cannot set a job queue blacklist '{%s}' in consumer since a whitelist '{%s}' is already defined.",
                    join(', ', $codes),
                    join(', ', $this->whitelistedJobInstanceCodes)
                )
            );
        }

        $this->blacklistedJobInstanceCodes = $codes;

        return $this;
    }

    public function setQueueCheckInterval(int $interval): Self
    {
        $this->queueCheckInterval = $interval;

        return $this;
    }

    public function setTimeToLive(int $iterations): Self
    {
        $this->timeToLive = $iterations;

        return $this;
    }

    public function offsetExists($offset)
    {
        return true === array_key_exists($offset, array_flip($this->listSupportedSettings));
    }

    public function offsetGet($offset)
    {
        switch ($offset) {
            case "whitelistedJobInstanceCodes":
                return $this->whitelistedJobInstanceCodes;
            case "blacklistedJobInstanceCodes":
                return $this->blacklistedJobInstanceCodes;
            case "timeToLive":
                return $this->timeToLive;
            case "queueCheckInterval":
                return $this->queueCheckInterval;
            default:
                throw new \RuntimeException(
                    sprintf(
                        "No such property '%s' in JobQueueConfiguration. Available properties are {%s}.",
                        $offset,
                        join(', ', $this->listSupportedSettings)
                    )
                );
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException("Please do use setters to set properties.");
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Cannot unset configutation properties.');
    }
}
