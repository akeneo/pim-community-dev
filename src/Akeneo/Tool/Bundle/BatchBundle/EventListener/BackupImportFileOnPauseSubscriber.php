<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BackupImportFileOnPauseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
    ) {}
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_STEP_EXECUTION_PAUSED => 'beforeStepExecutionPause'
        ];
    }

    public function beforeStepExecutionPause(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        if ($stepExecution->getJobExecution()->getJobInstance()->getType() !== 'import') {
            return;
        }

        $fileToBackUp = new JobFileLocation($stepExecution->getJobParameters()->get('storage')['file_path'], true);

        $backupFilePath = 'paused_job/step/' . $stepExecution->getId();
        $this->filesystemOperator->write($backupFilePath, file_get_contents($fileToBackUp->path()));
    }
}
