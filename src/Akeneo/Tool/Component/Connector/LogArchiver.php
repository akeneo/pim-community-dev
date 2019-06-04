<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use League\Flysystem\Filesystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogArchiver implements EventSubscriberInterface
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function archive(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        if (!$this->isImportOrExport($jobExecution)) {
            return;
        }

        $logPath = $jobExecution->getLogFile();

        if (is_file($logPath)) {
            $log = fopen($logPath, 'r');
            $this->filesystem->writeStream($logPath, $log);
            if (is_resource($log)) {
                fclose($log);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'archive'
        ];
    }

    private function isImportOrExport(JobExecution $jobExecution): bool
    {
        return in_array(
            $jobExecution->getJobInstance()->getType(),
            [JobInstance::TYPE_EXPORT, JobInstance::TYPE_IMPORT]
        );
    }
}
