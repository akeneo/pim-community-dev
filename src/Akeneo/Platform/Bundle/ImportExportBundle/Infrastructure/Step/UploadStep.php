<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Step;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\TransferFilesToStorageHandler;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber\UpdateJobExecutionStorageSummarySubscriber;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobWithStepsInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class UploadStep extends AbstractStep
{
    private const STORAGE_KEY = 'storage';

    private array $jobParameters = [];

    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        private JobRegistry $jobRegistry,
        private TransferFilesToStorageHandler $transferFilesToStorageHandler,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution)
    {
        $jobExecution = $stepExecution->getJobExecution();

        if (JobInstance::TYPE_EXPORT !== $jobExecution->getJobInstance()->getType()) {
            throw new \LogicException('Upload step should not be used for non export job.');
        }

        $this->jobParameters = $jobExecution->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $this->jobParameters)) {
            throw new \LogicException('malformed job parameters, missing storage configuration');
        }

        if (NoneStorage::TYPE === $this->jobParameters[self::STORAGE_KEY]['type']) {
            return;
        }

        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionStorageSummarySubscriber());
        $command = new TransferFilesToStorageCommand(
            $this->extractFileToTransfer($jobExecution),
            $this->jobParameters[self::STORAGE_KEY],
        );

        $this->transferFilesToStorageHandler->handle($command);
    }

    private function extractFileToTransfer(JobExecution $jobExecution): array
    {
        $writtenFiles = [];
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());

        if ($job instanceof JobWithStepsInterface) {
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
        }

        return $writtenFiles;
    }

    private function extractFileToTransferFromWriter(ArchivableWriterInterface $writer): array
    {
        $dirname = str_replace(sys_get_temp_dir(), '', dirname($writer->getPath()));

        return array_map(
            fn (WrittenFileInfo $writtenFile) => new FileToTransfer(
                $writtenFile->sourceKey(),
                $writtenFile->sourceStorage(),
                (LocalStorage::TYPE === $this->jobParameters[self::STORAGE_KEY]['type']) ? $writtenFile->outputFilepath() : sprintf('%s/%s', $dirname, $writtenFile->outputFilepath()),
                $writtenFile->isLocalFile()
            ),
            $writer->getWrittenFiles()
        );
    }
}
