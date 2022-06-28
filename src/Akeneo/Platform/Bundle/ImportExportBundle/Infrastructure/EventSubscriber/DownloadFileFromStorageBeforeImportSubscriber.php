<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\RemoteStorageFeatureFlag;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DownloadFileFromStorageBeforeImportSubscriber implements EventSubscriberInterface
{
    private const STORAGE_KEY = 'storage';

    public function __construct(
        private DownloadFileFromStorageHandler $downloadFileFromStorageHandler,
        private RemoteStorageFeatureFlag $remoteStorageFeatureFlag,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'downloadFileFromStorage',
        ];
    }

    public function downloadFileFromStorage(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        if (!$this->remoteStorageFeatureFlag->isEnabled($jobExecution->getJobInstance()->getJobName())) {
            return;
        }

        if (JobInstance::TYPE_IMPORT !== $jobExecution->getJobInstance()->getType()) {
            return;
        }

        $jobParameters = $jobExecution->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $jobParameters)) {
            return;
        }

        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionStorageSummarySubscriber());

        $command = new DownloadFileFromStorageCommand(
            $jobParameters[self::STORAGE_KEY],
            $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER),
        );

        $outputFilePath = $this->downloadFileFromStorageHandler->handle($command);

        $jobExecution->getJobParameters()->set('filePath', $outputFilePath);
    }
}
