<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered during job execution
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class JobExecutionEvent extends Event implements EventInterface
{
    public function __construct(private JobExecution $jobExecution)
    {
    }

    public function getJobExecution(): JobExecution
    {
        return $this->jobExecution;
    }
}
