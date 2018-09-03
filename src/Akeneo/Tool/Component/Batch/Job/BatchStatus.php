<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * Value object representing the status of a an Execution.
 *
 * Inspired by Spring Batch org.springframework.batch.core.BatchStatus
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchStatus
{
    const __DEFAULT = self::UNKNOWN;

    protected $value;

    /**
     * Constructor
     * @param integer $status
     */
    public function __construct($status = self::UNKNOWN)
    {
        $this->value = $status;
    }

    /**
     * The order of the status values is significant because it can be used to
     * aggregate a set of status values - the result should be the maximum
     * value. Since COMPLETED is first in the order, only if all elements of an
     * execution are COMPLETED will the aggregate status be COMPLETED. A running
     * execution is expected to move from STARTING to STARTED to COMPLETED
     * (through the order defined by {@link #upgradeTo(BatchStatus)}). Higher
     * values than STARTED signify more serious failure. ABANDONED is used for
     * steps that have finished processing, but were not successful, and where
     * they should be skipped on a restart (so FAILED is the wrong status).
     */
    const COMPLETED = 1;
    const STARTING = 2;
    const STARTED = 3;
    const STOPPING = 4;
    const STOPPED = 5;
    const FAILED = 6;
    const ABANDONED = 7;
    const UNKNOWN = 8;

    protected static $statusLabels = [
        self::COMPLETED => 'COMPLETED',
        self::STARTING  => 'STARTING',
        self::STARTED   => 'STARTED',
        self::STOPPING  => 'STOPPING',
        self::STOPPED   => 'STOPPED',
        self::FAILED    => 'FAILED',
        self::ABANDONED => 'ABANDONED',
        self::UNKNOWN   => 'UNKNOWN'
    ];

    /**
     * Get all labels associative array
     * @static
     *
     * @return array
     */
    public static function getAllLabels()
    {
        return array_flip(self::$statusLabels);
    }

    /**
     * Set the current status
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Return the current status value
     *
     * @return $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Convenience method to decide if a status indicates work is starting.
     *
     * @return boolean true if the status is STARTING
     */
    public function isStarting()
    {
        return $this->value == self::STARTING;
    }

    /**
     * Convenience method to decide if a status indicates work is in progress.
     *
     * @return boolean true if the status is STARTING, STARTED
     */
    public function isRunning()
    {
        return $this->value == self::STARTING || $this->value == self::STARTED;
    }

    /**
     * Convenience method to decide if a status indicates execution was
     * unsuccessful.
     *
     * @return boolean true if the status is FAILED or greater
     */
    public function isUnsuccessful()
    {
        return ($this->value == self::FAILED || $this->value > self::FAILED);
    }

    /**
     * Return the largest of two values
     *
     * @param integer $value1
     * @param integer $value2
     *
     * @return integer
     */
    public static function max($value1, $value2)
    {
        return max($value1, $value2);
    }

    /**
     * Method used to move status values through their logical progression, and
     * override less severe failures with more severe ones. This value is
     * compared with the parameter and the one that has higher priority is
     * returned. If both are STARTED or less than the value returned is the
     * largest in the sequence STARTING, STARTED, COMPLETED. Otherwise the value
     * returned is the maximum of the two.
     *
     * @param integer $otherStatus another status to compare to
     *
     * @return BatchStatus with either this or the other status depending on their priority
     */
    public function upgradeTo($otherStatus)
    {
        $newStatus = $this->value;

        if ($this->value > self::STARTED || $otherStatus > self::STARTED) {
            $newStatus = max($this->value, $otherStatus);
        } else {
            // Both less than or equal to STARTED
            if ($this->value == self::COMPLETED || $otherStatus == self::COMPLETED) {
                $newStatus = self::COMPLETED;
            } else {
                $newStatus = max($this->value, $otherStatus);
            }
        }
        $this->value = $newStatus;

        return $this;
    }

    /**
     * Return the string representation of the current status
     *
     * @return string
     */
    public function __toString()
    {
        return self::$statusLabels[$this->value];
    }
}
