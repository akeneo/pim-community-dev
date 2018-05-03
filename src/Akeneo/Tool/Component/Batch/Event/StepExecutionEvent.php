<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event triggered during stepExecution execution
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class StepExecutionEvent extends Event implements EventInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param StepExecution $stepExecution
     */
    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return StepExecution
     */
    public function getStepExecution()
    {
        return $this->stepExecution;
    }
}
