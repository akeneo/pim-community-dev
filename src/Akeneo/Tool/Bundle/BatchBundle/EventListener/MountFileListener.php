<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MountFileListener implements EventSubscriberInterface
{
    public function __construct(
        private FilesystemOperator $filesystemOperator,
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION_RESUME => 'beforeJobExecutionResume',
        ];
    }

    public function beforeJobExecutionResume(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        // here we need to check if there is a file to mount
        if ($jobExecution->getJobInstance()->getJobName() !== 'csv_attribute_import') {
            return;
        }

        $jobParameters = $jobExecution->getJobParameters();

        $filePathToMount = new JobFileLocation($jobParameters->get('storage')['file_path'], true);
        $backupFilePath = 'paused_job/job/' . $jobExecution->getId();

        // We are in the same storage for the Spike
        $this->filesystemOperator->copy($backupFilePath, $filePathToMount->path());
    }
}
