<?php

namespace Pim\Bundle\Batch2Bundle;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\Batch2Bundle\Model\Step;
use Pim\Bundle\Batch2Bundle\Model\Job;
use Pim\Bundle\Batch2Bundle\EventDispatching\DispatchingService;
use Pim\Bundle\Batch2Bundle\Model\ExecutionContext;
use Pim\Bundle\Batch2Bundle\Event\EventInterface;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Executor extends DispatchingService
{
    public function execute(Job $job)
    {
        $this->dispatchJobEvent(EventInterface::BEFORE_JOB_EXECUTION, $job);

        foreach ($job->getSteps() as $step) {
            $this->dispatchStepEvent(EventInterface::BEFORE_STEP_EXECUTION, $step);

            $reader    = $step->getReader();
            $processor = $step->getProcessor();
            $writer    = $step->getWriter();
            $context = new ExecutionContext();

            do {
                $reader->read($context);
                $processor->process($context);
                $writer->write($context);
            } while ($context->getItem() !== null);

            $this->dispatchStepEvent(EventInterface::AFTER_STEP_EXECUTION, $step);
        }

        $this->dispatchJobEvent(EventInterface::AFTER_JOB_EXECUTION, $job);
    }
}
