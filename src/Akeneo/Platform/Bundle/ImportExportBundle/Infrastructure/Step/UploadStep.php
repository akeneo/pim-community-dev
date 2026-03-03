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
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber\UpdateJobExecutionStorageSummarySubscriber;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class UploadStep extends AbstractStep
{
    private const STORAGE_KEY = 'storage';

    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        private TransferFilesToStorageHandler $transferFilesToStorageHandler,
        private FileWriterArchiver $fileWriterArchiver,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    protected function doExecute(StepExecution $stepExecution)
    {
        $jobExecution = $stepExecution->getJobExecution();

        if (JobInstance::TYPE_EXPORT !== $jobExecution->getJobInstance()->getType()) {
            throw new \LogicException('Upload step should not be used for non export job.');
        }

        $jobParameters = $jobExecution->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $jobParameters)) {
            throw new \LogicException('malformed job parameters, missing storage configuration');
        }

        if (NoneStorage::TYPE === $jobParameters[self::STORAGE_KEY]['type']) {
            return;
        }

        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionStorageSummarySubscriber());
        $command = new TransferFilesToStorageCommand(
            $this->extractFileToTransfer($jobExecution),
            $jobParameters[self::STORAGE_KEY],
        );

        $this->transferFilesToStorageHandler->handle($command);
    }

    private function extractFileToTransfer(JobExecution $jobExecution): array
    {
        $archiveDirectoryPath = $this->fileWriterArchiver->getArchiveDirectoryPath($jobExecution);
        $destinationDirname = dirname($this->getDestinationPath($jobExecution));

        return array_map(static fn (string $filePath) => new FileToTransfer(
            $filePath,
            'archivist',
            $destinationDirname.substr($filePath, strlen($archiveDirectoryPath)),
            false
        ), iterator_to_array($this->fileWriterArchiver->getArchives($jobExecution, true)));
    }

    private function getDestinationPath(JobExecution $jobExecution): string
    {
        $parameters = $jobExecution->getJobParameters();

        return $parameters->get('storage')['file_path'];
    }
}
