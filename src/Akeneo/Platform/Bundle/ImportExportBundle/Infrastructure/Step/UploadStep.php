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
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class UploadStep extends AbstractStep
{
    private const STORAGE_KEY = 'storage';

    private array $jobParameters = [];

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
        $filePath = strtr(
            $this->fileWriterArchiver->getRelativeArchivePath($jobExecution),
            [
                '%filename%' => '',
            ]
        );

        $dirname = str_replace(sys_get_temp_dir(), '', dirname($this->getPath($jobExecution)));

        return array_map(function ($item) use ($dirname, $filePath) {
            return new FileToTransfer(
                $item,
                'archivist',
                $dirname . DIRECTORY_SEPARATOR . str_replace($filePath, '', $item),
                false
            );
        }, iterator_to_array($this->fileWriterArchiver->getArchives($jobExecution, true)));
    }

    public function getPath(JobExecution $jobExecution): string
    {
        $parameters = $jobExecution->getJobParameters();
        $storage = $parameters->get('storage');
        return sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, $storage['file_path']);
    }
}
