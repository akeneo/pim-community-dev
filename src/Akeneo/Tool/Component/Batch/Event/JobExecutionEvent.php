<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered during job execution
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecutionEvent extends Event implements EventInterface
{
    /** @var JobExecution */
    protected $jobExecution;

    /**
     * @param JobExecution $jobExecution
     */
    public function __construct(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;
    }

    /**
     * @return JobExecution
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }
}
