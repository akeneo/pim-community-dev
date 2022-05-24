<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExportFileToStorageAfterExportSubscriber implements EventSubscriberInterface
{
    private const STORAGE_KEY = 'storage';

    public function __construct(
        private JobRegistry $jobRegistry,
        private TransferFilesToStorageHandler $transferFilesToStorageHandler,
        private FeatureFlags $featureFlags,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'exportFileToStorage',
        ];
    }

    public function exportFileToStorage(JobExecutionEvent $event): void
    {
        if (!$this->featureFlags->isEnabled('job_automation_remote_storage')) {
            return;
        }

        $jobExecution = $event->getJobExecution();
        if (JobInstance::TYPE_EXPORT !== $jobExecution->getJobInstance()->getType()) {
            return;
        }

        $jobParameters = $jobExecution->getJobInstance()->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $jobParameters)) {
            return;
        }

        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionStorageSummarySubscriber($jobExecution));
        $command = new TransferFilesToStorageCommand(
            $this->extractFileToTransfer($jobExecution),
            $jobParameters[self::STORAGE_KEY],
        );

        $this->transferFilesToStorageHandler->handle($command);
    }

    private function extractFileToTransfer(JobExecution $jobExecution): array
    {
        $writtenFiles = [];
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }

            $writer = $step->getWriter();
            if (!$writer instanceof ArchivableWriterInterface) {
                continue;
            }

            $writtenFiles = array_merge($writtenFiles, $this->extractFileToTransferFromWriter($writer));
        }

        return $writtenFiles;
    }

    private function extractFileToTransferFromWriter(ArchivableWriterInterface $writer): array
    {
        return array_map(
            static fn (WrittenFileInfo $writtenFile) => new FileToTransfer(
                $writtenFile->sourceKey(),
                $writtenFile->sourceStorage(),
                $writtenFile->outputFilepath(),
            ),
            $writer->getWrittenFiles()
        );
    }
}
