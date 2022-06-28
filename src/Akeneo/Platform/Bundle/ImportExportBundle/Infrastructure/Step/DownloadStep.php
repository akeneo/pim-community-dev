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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Step;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageCommand;
use Akeneo\Platform\Bundle\ImportExportBundle\Application\DownloadFileFromStorage\DownloadFileFromStorageHandler;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\EventSubscriber\UpdateJobExecutionStorageSummarySubscriber;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\RemoteStorageFeatureFlag;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DownloadStep extends AbstractStep
{
    private const STORAGE_KEY = 'storage';

    private DownloadFileFromStorageHandler $downloadFileFromStorageHandler;

    public function __construct(
        string $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        DownloadFileFromStorageHandler $downloadFileFromStorageHandler,
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->downloadFileFromStorageHandler = $downloadFileFromStorageHandler;
    }

    public function doExecute(StepExecution $stepExecution)
    {
        $jobExecution = $stepExecution->getJobExecution();

        if (JobInstance::TYPE_IMPORT !== $jobExecution->getJobInstance()->getType()) {
            throw new \Exception('Download step should not be used for non import job.');
        }

        $jobParameters = $jobExecution->getRawParameters();
        if (!array_key_exists(self::STORAGE_KEY, $jobParameters)) {
            throw new \Exception('malformed job parameters, missing storage configuration');
        }

        if ('local' === $jobParameters[self::STORAGE_KEY]['type'] || 'none' === $jobParameters[self::STORAGE_KEY]['type']) {
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
