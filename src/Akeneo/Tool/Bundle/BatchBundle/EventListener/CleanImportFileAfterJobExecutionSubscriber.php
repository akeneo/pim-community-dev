<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CleanImportFileAfterJobExecutionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobFileBackuper $jobFileBackuper
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::AFTER_JOB_EXECUTION => 'cleanImportFile'
        ];
    }

    public function cleanImportFile(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        if ($jobExecution->getJobInstance()->getType() !== 'import' || $jobExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
            return;
        }

        $this->jobFileBackuper->clean($jobExecution);
    }
}
