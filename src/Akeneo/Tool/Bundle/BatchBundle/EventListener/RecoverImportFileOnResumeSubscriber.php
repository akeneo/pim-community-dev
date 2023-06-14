<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecoverImportFileOnResumeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobFileBackuper $jobFileBackuper,
    ) {
    }
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_STEP_EXECUTION_RESUME => 'recoverImportFile'
        ];
    }

    public function recoverImportFile(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        if ($stepExecution->getJobExecution()->getJobInstance()->getType() !== 'import') {
            return;
        }

        $storage = $stepExecution->getJobParameters()->get('storage');
        $localFilePath = sprintf(
            '%s%s',
            $stepExecution->getJobExecution()->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER),
            basename($storage['file_path']),
        );

        $this->jobFileBackuper->recover($stepExecution->getJobExecution(), $localFilePath);

        $storage['file_path'] = $localFilePath;
        $stepExecution->getJobParameters()->set('storage', $storage);
    }
}
