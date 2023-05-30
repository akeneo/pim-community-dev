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

    const PAUSED = "PAUSED";

    /** @var int[] */
    protected static array $statusSeverity = [
        self::EXECUTING => 1,
        self::PAUSED => 2,
        self::COMPLETED => 3,
        self::NOOP      => 4,
        self::STOPPED   => 5,
        self::FAILED    => 6,
        self::UNKNOWN   => 7
    ];

    private string $exitCode;
    private string $exitDescription = "";

    public function __construct(string $exitCode = self::UNKNOWN, string $exitDescription = "")
    {
        $this->exitCode = $exitCode;
        $this->exitDescription = $exitDescription;
    }

    public function getExitCode(): string
    {
        return $this->exitCode;
    }

    public function getExitDescription(): string
    {
        return $this->exitDescription;
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
     */
    public function logicalAnd(ExitStatus $status): self
    {
        $this->addExitDescription($status->exitDescription);
        if ($this->compareTo($status) < 0) {
            $this->exitCode = $status->exitCode;
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
    public function compareTo(ExitStatus $status): int
    {
        if ($status->severity() > $this->severity()) {
            return -1;
        }
        if ($status->severity() < $this->severity()) {
            return 1;
        }

        return 0;
    }

    private function severity(): int
    {
        $severity = self::MAX_SEVERITY;

        if (array_key_exists($this->exitCode, self::$statusSeverity)) {
            $severity = self::$statusSeverity[$this->exitCode];
        }

        return $severity;
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s', $this->exitCode, $this->exitDescription);
    }

    public function isRunning(): bool
    {
        return ((self::EXECUTING ===  $this->exitCode) || (self::UNKNOWN === $this->exitCode));
    }

    /**
     * Add an exit description to an existing {@link ExitStatus}. If there is
     * already a description present the two will be concatenated with a
     * semicolon.
     *
     * @param string|\Exception $description the description to add. Can be an exception.
     *                            In this case, the stack trace is used as description
     */
    public function addExitDescription(string|\Exception|null $description): self
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
