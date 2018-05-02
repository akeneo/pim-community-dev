<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * Value object used to carry information about the status of a
 * job or step execution.
 *
 * Inspired by Spring Batch org.springframework.batch.core.ExitStatus;
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ExitStatus
{
    const MAX_SEVERITY = 7;

    /**
     * Convenient constant value representing unknown state - assumed not
     * continuable.
     */
    const UNKNOWN = "UNKNOWN";

    /**
     * Convenient constant value representing continuable state where processing
     * is still taking place, so no further action is required. Used for
     * asynchronous execution scenarios where the processing is happening in
     * another thread or process and the caller is not required to wait for the
     * result.
     */
    const EXECUTING = "EXECUTING";

    /**
     * Convenient constant value representing finished processing.
     */
    const COMPLETED = "COMPLETED";

    /**
     * Convenient constant value representing job that did no processing (e.g.
     * because it was already complete).
     */
    const NOOP = "NOOP";

    /**
     * Convenient constant value representing finished processing with an error.
     */
    const FAILED = "FAILED";

    /**
     * Convenient constant value representing finished processing with
     * interrupted status.
     */
    const STOPPED = "STOPPED";

    protected static $statusSeverity = [
        self::EXECUTING => 1,
        self::COMPLETED => 2,
        self::NOOP      => 3,
        self::STOPPED   => 4,
        self::FAILED    => 5,
        self::UNKNOWN   => 6
    ];

    private $exitCode;
    private $exitDescription;

    /**
     * Constructor
     *
     * @param integer $exitCode        Code for the exit status
     * @param string  $exitDescription Description of the exit status
     */
    public function __construct($exitCode = self::UNKNOWN, $exitDescription = "")
    {
        $this->exitCode = $exitCode;
        $this->exitDescription = $exitDescription;
    }

    /**
     * Getter for the exit code (defaults to blank).
     *
     * @return the exit code.
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * Getter for the exit description (defaults to blank)
     *
     * @return string
     */
    public function getExitDescription()
    {
        return $this->exitDescription;
    }

    /**
     * Set the current status
     * @param integer $exitCode
     *
     * @return ExitStatus
     */
    public function setExitCode($exitCode)
    {
        if ($exitCode > self::UNKNOWN) {
            $this->exitCode = self::UNKNOWN;
        } else {
            $this->exitCode = $exitCode;
        }

        return $this;
    }

    /**
     * Create a new {@link ExitStatus} with a logical combination of the exit
     * code, and a concatenation of the descriptions. If either value has a
     * higher severity then its exit code will be used in the result.
     *
     * Severity is defined by the exit code.
     * <ul>
     * <li>Codes beginning with EXECUTING have severity 1</li>
     * <li>Codes beginning with COMPLETED have severity 2</li>
     * <li>Codes beginning with NOOP have severity 3</li>
     * <li>Codes beginning with STOPPED have severity 4</li>
     * <li>Codes beginning with FAILED have severity 5</li>
     * <li>Codes beginning with UNKNOWN have severity 6</li>
     * </ul>
     * Others have severity 7, so custom exit codes always win.<br/>
     *
     * If the input is null just return this.
     *
     * @param ExitStatus $status an {@link ExitStatus} to combine with this one.
     *
     * @return ExitStatus a new {@link ExitStatus} combining the current value and the argument provided.
     */
    public function logicalAnd(ExitStatus $status)
    {
        if ($status != null) {
            $this->addExitDescription($status->exitDescription);
            if ($this->compareTo($status) < 0) {
                $this->exitCode = $status->exitCode;
            }
        }

        return $this;
    }

    /**
     * Compare ExitStatus with another one
     *
     * @param ExitStatus $status an {@link ExitStatus} to compare
     *
     * @return 1,0,-1 according to the severity and exit code
     */
    public function compareTo(ExitStatus $status)
    {
        if ($status->severity() > $this->severity()) {
            return -1;
        }
        if ($status->severity() < $this->severity()) {
            return 1;
        }

        return 0;
    }

    /**
     * Return the severity of the current status
     *
     * @return severity
     */
    private function severity()
    {
        $severity = self::MAX_SEVERITY;

        if (array_key_exists($this->exitCode, self::$statusSeverity)) {
            $severity = self::$statusSeverity[$this->exitCode];
        }

        return $severity;
    }

    /**
     * Return the string representation of the current status
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%s] %s', $this->exitCode, $this->exitDescription);
    }

    /**
     * Check if this status represents a running process.
     *
     * @return boolean true if the exit code is "EXECUTING" or "UNKNOWN"
     */
    public function isRunning()
    {
        return ((self::EXECUTING ===  $this->exitCode) || (self::UNKNOWN === $this->exitCode));
    }

    /**
     * Add an exit description to an existing {@link ExitStatus}. If there is
     * already a description present the two will be concatenated with a
     * semicolon.
     *
     * @param string $description the description to add. Can be an exception.
     *                            In this case, the stack trace is used as description
     *
     * @return ExitStatus a new {@link ExitStatus} with the same properties but a new exit description
     */
    public function addExitDescription($description)
    {
        if ($description instanceof \Exception) {
            $description = $description->getTraceAsString();
        }

        if (!empty($description) && $this->exitDescription != $description) {
            if (!empty($this->exitDescription)) {
                $this->exitDescription .= ';';
            }
            $this->exitDescription .= $description;
        }

        return $this;
    }
}
