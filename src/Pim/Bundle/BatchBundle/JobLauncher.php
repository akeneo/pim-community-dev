<?php

namespace Pim\Bundle\BatchBundle;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\BatchBundle\Model\Step;
use Pim\Bundle\BatchBundle\Model\Job;
use Pim\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BatchBundle\Event\JobEvent;
use Pim\Bundle\BatchBundle\Event\StepEvent;
use Pim\Bundle\BatchBundle\Event\ItemEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobLauncher
{
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function launch(Job $job)
    {
        $this->dispatchJobEvent(EventInterface::BEFORE_JOB_EXECUTION, $job);

        foreach ($job->getSteps() as $step) {
            $this->dispatchStepEvent(EventInterface::BEFORE_STEP_EXECUTION, $step);

            $reader    = $step->getReader();
            $processor = $step->getProcessor();
            $writer    = $step->getWriter();

            while (null !== $item = $reader->read()) {
                $this->dispatchItemEvent(EventInterface::AFTER_READ, $item);

                $item = $processor->process($item);
                $this->dispatchItemEvent(EventInterface::AFTER_PROCESS, $item);

                $writer->write($item);
                $this->dispatchItemEvent(EventInterface::AFTER_WRITE, $item);
            }

            $this->dispatchStepEvent(EventInterface::AFTER_STEP_EXECUTION, $step);
        }

        $this->dispatchJobEvent(EventInterface::AFTER_JOB_EXECUTION, $job);
    }

    protected function dispatchJobEvent($eventName, Job $job)
    {
        $event = new JobEvent($job);
        $this->dispatch($eventName, $event);
    }

    protected function dispatchStepEvent($eventName, Step $step)
    {
        $event = new StepEvent($step);
        $this->dispatch($eventName, $event);
    }

    protected function dispatchItemEvent($eventName, $item, $result = ItemEvent::PASSED)
    {
        $event = new ItemEvent($item, $result);
        $this->dispatch($eventName, $event);
    }

    private function dispatch($eventName, Event $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
    }
}
