<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\UpdateProductFileImportStatusFromJobStatus;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateProductFileImportOnJobExecutionEvent implements EventSubscriberInterface
{
    public function __construct(private UpdateProductFileImportStatusFromJobStatus $updateProductFileImportStatus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'updateProductFileImportStatus',
            EventInterface::JOB_EXECUTION_INTERRUPTED => 'updateProductFileImportStatus',
            EventInterface::JOB_EXECUTION_FATAL_ERROR => 'updateProductFileImportStatus',
        ];
    }

    public function updateProductFileImportStatus(JobExecutionEvent $jobExecutionEvent): void
    {
        $jobExecutionStatus = $jobExecutionEvent->getJobExecution()->getStatus()->getValue();
        if (!\in_array($jobExecutionStatus, [
            BatchStatus::COMPLETED,
            BatchStatus::STOPPING,
            BatchStatus::STOPPED,
            BatchStatus::FAILED,
            BatchStatus::ABANDONED,
            BatchStatus::UNKNOWN,
        ])) {
            return;
        }

        ($this->updateProductFileImportStatus)(
            $jobExecutionStatus,
            $jobExecutionEvent->getJobExecution()->getId()
        );
    }
}
