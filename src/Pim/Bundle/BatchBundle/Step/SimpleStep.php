<?php

namespace Pim\Bundle\BatchBundle\Step;

/**
 * Interface with constant representing the status of a an Execution.
 *
 * Inspired by Spring Batch org.springframework.batch.core.BatchStatus
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SimpleStep
{
    const __DEFAULT = self::UNKNOWN;

    protected $value;

    /**
     * Constructor
     * @param mixed $status
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
    const STARTING  = 2;
    const STARTED   = 3;
    const STOPPING  = 4;
    const STOPPED   = 5;
    const FAILED    = 6;
    const ABANDONED = 7;
    const UNKNOWN   = 8;

    protected static $statusLabels = array (
        self::COMPLETED => 'COMPLETED',
        self::STARTING  => 'STARTING',
        self::STARTED   => 'STARTED',
        self::STOPPING  => 'STOPPING',
        self::STOPPED   => 'STOPPED',
        self::FAILED    => 'FAILED',
        self::ABANDONED => 'ABANDONED',
        self::UNKNOWN   => 'UNKNOWN'
    );

    /**
     * Set the current status
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
     * Return batch status with the highest value
     * @param BatchStatus $status1
     * @param BatchStatus $status2
     *
     * @return BatchStatus
     */
    public static function max(BatchStatus $status1, BatchStatus $status2)
    {
        return $status1->value > $status2->value ? $status1 : $status2;
    }

    /**
     * Convenience method to decide if a status indicates work is in progress.
     *
     * @return true if the status is STARTING, STARTED
     */
    public function isRunning()
    {
        return $this->value == self::STARTING || $this->value == self::STARTED;
    }

    /**
     * Convenience method to decide if a status indicates execution was
     * unsuccessful.
     *
     * @return true if the status is FAILED or greater
     */
    public function isUnsuccessful()
    {
        return ($this->value == self::FAILED || $this->value > self::FAILED);
    }

    /**
     * Method used to move status values through their logical progression, and
     * override less severe failures with more severe ones. This value is
     * compared with the parameter and the one that has higher priority is
     * returned. If both are STARTED or less than the value returned is the
     * largest in the sequence STARTING, STARTED, COMPLETED. Otherwise the value
     * returned is the maximum of the two.
     *
     * @param BatchStatus $other another status to compare to
     *
     * @return BatchStatus either this or the other status depending on their priority
     */
    public function upgradeTo(BatchStatus $other)
    {
        if ($this->value > self::STARTED || $other->value > self::STARTED) {
            return max($this, $other);
        }
        // Both less than or equal to STARTED
        if ($this->value == self::COMPLETED || $other->value == self::COMPLETED) {
            return new self(self::COMPLETED);
        }

        return self::max($this, $other);
    }

    /**
     * Return the string representation of the current status
     * @return string
     */
    public function __toString()
    {
        return self::$statusLabels[$this->value];
    }
}
