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

use Akeneo\Platform\Bundle\ImportExportBundle\Application\ResolveTransferServiceAndUpload\ResolveTransferServiceAndUploadCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\ResolveTransferServiceAndUpload\ResolveTransferServiceAndUploadHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;

class ExportFileToStorageSubscriber
{
    public function __construct(
        private JobRegistry $jobRegistry,
        private ResolveTransferServiceAndUploadHandler $resolveTransferServiceAndUploadHandler,
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
        $jobExecution = $event->getJobExecution();
        if (JobInstance::TYPE_EXPORT !== $jobExecution->getJobInstance()->getType()) {
            return;
        }

        $jobParameters = $jobExecution->getJobInstance()->getRawParameters();
        if (!array_key_exists('storage', $jobParameters)) {
            return;
        }

        $command = new ResolveTransferServiceAndUploadCommand();
        $command->writtenFilesInfo = $this->getWrittenFiles($jobExecution);;
        $command->storageInformation = $jobParameters['storage'];

        $this->resolveTransferServiceAndUploadHandler->handle($command);
    }

    private function getWrittenFiles(JobExecution $jobExecution): array
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

            $writtenFiles = array_merge($writtenFiles, $writer->getWrittenFiles());
        }

        return $writtenFiles;
    }
}
