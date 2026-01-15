<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Contracts\EventDispatcher\Event;

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

    protected ?\Exception  $exception;

    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    public function __construct(StepExecution $stepExecution, ?\Exception $exception=null)
    {
        $this->stepExecution = $stepExecution;
        $this->exception = $exception;
    }

    /**
     * @return StepExecution
     */
    public function getStepExecution()
    {
        return $this->stepExecution;
    }
}
