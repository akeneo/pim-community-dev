<?php

namespace Pim\Bundle\Batch2Bundle\EventDispatching;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\Batch2Bundle\Model\Job;
use Pim\Bundle\Batch2Bundle\Model\Step;
use Pim\Bundle\Batch2Bundle\Event\JobEvent;
use Pim\Bundle\Batch2Bundle\Event\StepEvent;
use Pim\Bundle\Batch2Bundle\Event\ItemEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DispatchingService
{
    protected $dispatcher;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
