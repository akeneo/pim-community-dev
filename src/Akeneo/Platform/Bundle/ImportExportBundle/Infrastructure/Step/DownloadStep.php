<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Step;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber\UpdateJobExecutionStorageSummarySubscriber;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DownloadStep extends AbstractStep
{
    private const STORAGE_KEY = 'storage';

    public function __construct(
        string $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        private DownloadFileFromStorageHandler $downloadFileFromStorageHandler,
        private readonly string $localStorageRoot,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
    }

    public function doExecute(StepExecution $stepExecution)
    {
        $jobExecution = $stepExecution->getJobExecution();

        if (JobInstance::TYPE_IMPORT !== $jobExecution->getJobInstance()->getType()) {
            throw new \LogicException('Download step should not be used for non import job.');
        }

        $jobParameters = $jobExecution->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $jobParameters)) {
            throw new \LogicException('malformed job parameters, missing storage configuration');
        }

        if (NoneStorage::TYPE === $jobParameters[self::STORAGE_KEY]['type']) {
            return;
        }

        $this->eventDispatcher->addSubscriber(new UpdateJobExecutionStorageSummarySubscriber());
        $command = new DownloadFileFromStorageCommand(
            $jobParameters[self::STORAGE_KEY],
            $jobExecution->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER),
        );

        $relativeOutputFilePath = $this->downloadFileFromStorageHandler->handle($command);
        $absoluteOutputFilePath = str_replace('//', '/', $this->localStorageRoot.$relativeOutputFilePath);

        $storage = [
            'type' => LocalStorage::TYPE,
            'file_path' => $absoluteOutputFilePath,
        ];
        $jobExecution->getJobParameters()->set('storage', $storage);
    }
}
