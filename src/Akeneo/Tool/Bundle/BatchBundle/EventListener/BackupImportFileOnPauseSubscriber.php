<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BackupImportFileOnPauseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobFileBackuper $jobFileBackuper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_STEP_EXECUTION_PAUSED => 'backupImportFile',
        ];
    }

    public function backupImportFile(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        if ($stepExecution->getJobExecution()->getJobInstance()->getType() !== JobInstance::TYPE_IMPORT) {
            return;
        }

        $localFilePath = $stepExecution->getJobParameters()->get('storage')['file_path'];
        $this->jobFileBackuper->backup($stepExecution->getJobExecution(), $localFilePath);
    }
}
