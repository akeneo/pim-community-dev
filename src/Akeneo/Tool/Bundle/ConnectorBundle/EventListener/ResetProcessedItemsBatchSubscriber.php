<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reset the processed items saved into the job execution context after each batch executed during a step.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetProcessedItemsBatchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::ITEM_STEP_AFTER_BATCH => 'resetProcessedItemsBatch',
        ];
    }

    public function resetProcessedItemsBatch(StepExecutionEvent $event): void
    {
        $event->getStepExecution()->getExecutionContext()->remove('processed_items_batch');
    }
}
