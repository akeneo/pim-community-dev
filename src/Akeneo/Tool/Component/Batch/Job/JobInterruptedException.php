<?php

namespace Akeneo\Tool\Component\Batch\Job;

/**
 * Exception to indicate the the job has been interrupted. The exception state
 * indicated is not normally recoverable by batch application clients, but
 * internally it is useful to force a check. The exception will often be wrapped
 * in a runtime exception (usually UnexpectedJobExecutionException} before
 * reaching the client.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobInterruptedException extends \Exception
{
    private $status;

    /**
     * Constructor
     * @param string      $message  Execption message
     * @param integer     $code     Execption code
     * @param \Exception  $previous Exception causing this one
     * @param BatchStatus $status   Status of the batch when the execption occurred
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, BatchStatus $status = null)
    {
        parent::__construct($message, $code, $previous);

        if ($status) {
            $this->status = $status;
        } else {
            $this->status = new BatchStatus(BatchStatus::STOPPED);
        }
    }

    /**
     * The desired status of the surrounding execution after the interruption.
     *
     * @return BatchStatus the status of the interruption (default STOPPED)
     */
    public function getStatus()
    {
        return $this->status;
    }
}
