<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
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

        $storage = $jobExecution->getJobParameters()->get('storage');

        // should we re-use download file from storage handler?
        $filePathToMount = $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER) . basename($storage['file_path']);
        $backupFilePath = 'paused_job/job/' . $jobExecution->getId();
        file_put_contents($filePathToMount, $this->filesystemOperator->readStream($backupFilePath));
        $storage['file_path'] = $filePathToMount;
        $jobExecution->getJobParameters()->set('storage', $storage);
    }
}
