<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Subscriber\UpdateProductFileImportOnJobExecutionEvent;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\UpdateProductFileImportStatusFromJobStatus;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\TestCase;

final class UpdateProductFileImportOnJobExecutionEventTest extends TestCase
{
    /** @test */
    public function itSubscribesToEvents(): void
    {
        $updateProductFileImportStatusFromJobStatus = $this->createMock(UpdateProductFileImportStatusFromJobStatus::class);
        $sut = new UpdateProductFileImportOnJobExecutionEvent($updateProductFileImportStatusFromJobStatus);

        $this->assertSame([
            EventInterface::AFTER_JOB_EXECUTION => 'updateProductFileImportStatus',
            EventInterface::JOB_EXECUTION_INTERRUPTED => 'updateProductFileImportStatus',
            EventInterface::JOB_EXECUTION_FATAL_ERROR => 'updateProductFileImportStatus',
        ], $sut::getSubscribedEvents());
    }

    /** @test */
    public function itDoesNotUpdateProductFileImportIfStatusIsNotAllowed(): void
    {
        $jobExecutionEvent = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $updateProductFileImportStatusFromJobStatus = $this->createMock(UpdateProductFileImportStatusFromJobStatus::class);

        $jobExecutionEvent->expects($this->once())
            ->method('getJobExecution')
            ->willReturn($jobExecution);

        $batchStatus = new BatchStatus(BatchStatus::STARTING);
        $jobExecution->expects($this->once())
            ->method('getStatus')
            ->willReturn($batchStatus);

        $sut = new UpdateProductFileImportOnJobExecutionEvent($updateProductFileImportStatusFromJobStatus);

        $updateProductFileImportStatusFromJobStatus
            ->expects($this->never())
            ->method('__invoke');

        $sut->updateProductFileImportStatus($jobExecutionEvent);
    }
    /** @test */
    public function itUpdatesProductFileImportIfStatusIsAllowed(): void
    {
        $jobExecutionEvent = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $updateProductFileImportStatusFromJobStatus = $this->createMock(UpdateProductFileImportStatusFromJobStatus::class);

        $jobExecutionEvent->expects($this->any())
            ->method('getJobExecution')
            ->willReturn($jobExecution);

        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->expects($this->any())
            ->method('getStatus')
            ->willReturn($batchStatus);
        $jobExecution->expects($this->once())
            ->method('getId')
            ->willReturn(42);

        $sut = new UpdateProductFileImportOnJobExecutionEvent($updateProductFileImportStatusFromJobStatus);

        $updateProductFileImportStatusFromJobStatus
            ->expects($this->once())
            ->method('__invoke')
            ->with($batchStatus->getValue(), 42);

        $sut->updateProductFileImportStatus($jobExecutionEvent);
    }
}
