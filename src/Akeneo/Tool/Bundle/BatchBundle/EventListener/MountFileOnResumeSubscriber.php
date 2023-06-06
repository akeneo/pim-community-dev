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
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MountFileOnResumeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
    ) {}
    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_STEP_EXECUTION_RESUME => 'beforeStepExecutionResume'
        ];
    }

    public function beforeStepExecutionResume(StepExecutionEvent $event): void
    {
        $stepExecution = $event->getStepExecution();

        if ($stepExecution->getJobExecution()->getJobInstance()->getType() !== 'import') {
            return;
        }

        $storage = $stepExecution->getJobParameters()->get('storage');

        $filePathToMount = $stepExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER) . basename($storage['file_path']);
        $backupFilePath = 'paused_job/step/' . $stepExecution->getId();
        file_put_contents($filePathToMount, $this->filesystemOperator->readStream($backupFilePath));
        $storage['file_path'] = $filePathToMount;
        $stepExecution->getJobParameters()->set('storage', $storage);
    }
}
