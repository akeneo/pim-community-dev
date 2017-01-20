<?php

namespace Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Given a job execution processing multiple entities and keeping track of their identifiers in the BulkIdentifierBag
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

    public static function getSubscribedEvents()
    {
        return [
            EventInterface::JOB_BATCH_SIZE_REACHED => 'resetJobExecutionBulkIdentifierBar'
        ];
    }

    public function resetJobExecutionBulkIdentifierBar(JobExecutionEvent $jobExecutionEvent)
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();
        $executionContext = $jobExecution->getExecutionContext();
        $bulkIdentifierBag = $executionContext->get('bulk_identifier_bag');

        if (null !== $bulkIdentifierBag) {
            $bulkIdentifierBag->reset();
        }
    }
}
