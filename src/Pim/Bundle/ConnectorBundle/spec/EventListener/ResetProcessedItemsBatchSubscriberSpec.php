<?php

namespace spec\Pim\Bundle\ConnectorBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Component\Batch\Item\ExecutionContext;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResetProcessedItemsBatchSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    function it_returns_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                EventInterface::ITEM_STEP_AFTER_BATCH => 'resetProcessedItemsBatch'
            ]
        );
    }

    function it_resets_processed_items_batch_saved_in_the_execution_context(
        StepExecutionEvent $event,
        StepExecution $stepExecution,
        ExecutionContext $executionContext
    ) {
        $event->getStepExecution()->willReturn($stepExecution);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $executionContext->remove('processed_items_batch')->shouldBeCalled();

        $this->resetProcessedItemsBatch($event);
    }
}
