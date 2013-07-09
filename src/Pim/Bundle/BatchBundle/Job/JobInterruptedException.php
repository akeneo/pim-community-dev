<?php

namespace Pim\Bundle\BatchBundle\Item;

/**
 *
 * Exception to indicate the the job has been interrupted. The exception state
 * indicated is not normally recoverable by batch application clients, but
 * internally it is useful to force a check. The exception will often be wrapped
 * in a runtime exception (usually UnexpectedJobExecutionException} before
 * reaching the client.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobInterruptedException extends \Exception
{
    private $status;

    public function __construct($message = "",
                                $code = 0,
                                \Exception $previous = null,
                                BatchStatus $status = null) {
        parent($message, $code, $exception);

        if ($status != null) {
            $this->status = $status;
        } else {
            $this->status = new BatchStatus(BatchStatus::STOPPED);
        }
    }


    /**
     * The desired status of the surrounding execution after the interruption.
     * 
     * @return the status of the interruption (default STOPPED)
     */
    public function getStatus()
    {
        return $this->status;
    }

}
