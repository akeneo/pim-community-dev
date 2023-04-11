<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BackupFileListener implements EventSubscriberInterface
{
    public function __construct(
        private FilesystemOperator $filesystemOperator,
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION_PAUSE => 'beforeJobExecutionPause',
        ];
    }

    public function beforeJobExecutionPause(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        // here we need to check if there is a file to back-up
        if ($jobExecution->getJobInstance()->getJobName() !== 'csv_attribute_import') {
            return;
        }

        $fileToBackUp = new JobFileLocation($jobExecution->getJobParameters()->get('storage')['file_path'], true);

        $backupFilePath = 'paused_job/job/' . $jobExecution->getId();
        $this->filesystemOperator->write($backupFilePath, file_get_contents($fileToBackUp->path()));
    }
}
