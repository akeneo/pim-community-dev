<?php

namespace Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\StepExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Given a job processing multiple entities and keeping track of their identifiers in the BulkIdentifierBag
 * (to prevent the processing of duplicated entities).
 * This event subscriber resets the list of identifiers treated once a full batch has been processed (the batch size
 * is configured at the job level).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetBulkIdentifierBagSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::JOB_BATCH_SIZE_REACHED => 'resetJobExecutionBulkIdentifierBar',
        ];
    }

    /**
     * Once a step has reached the job batch size. This event subscriber resets the bulk identifier bag if it exists.
     */
    public function resetJobExecutionBulkIdentifierBar(StepExecutionEvent $stepExecutionEvent)
    {
        $stepExecution = $stepExecutionEvent->getStepExecution();
        $executionContext = $stepExecution->getExecutionContext();
        $bulkIdentifierBag = $executionContext->get('bulk_identifier_bag');

        if (null !== $bulkIdentifierBag) {
            $bulkIdentifierBag->reset();
        }
    }
}
