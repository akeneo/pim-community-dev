<?php

namespace Akeneo\Bundle\BatchBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Event triggered during job execution
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecutionEvent extends Event implements EventInterface
{
    protected $jobExecution;

    public function __construct(JobExecution $jobExecution)
    {
        $this->jobExecution = $jobExecution;
    }

    public function getJobExecution()
    {
        return $this->jobExecution;
    }
}
