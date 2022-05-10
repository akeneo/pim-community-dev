<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExportFileToStorageAfterExportSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private JobRegistry $jobRegistry,
        private TransferFilesToStorageHandler $transferFilesToStorageHandler,
        private FeatureFlags $featureFlags
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
        if (!array_key_exists('storage', $jobParameters)) {
            return;
        }

        $command = new TransferFilesToStorageCommand(
            $this->extractFileToTransfer($jobExecution),
            $jobParameters['storage'],
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
                $writtenFile->isLocalFile()
            ), $writer->getWrittenFiles()
        );
    }
}
