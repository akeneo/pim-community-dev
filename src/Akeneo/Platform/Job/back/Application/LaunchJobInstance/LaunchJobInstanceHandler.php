<?php

namespace Akeneo\Platform\Job\Application\LaunchJobInstance;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Job\Domain\JobFileStorerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceHandlerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;

class LaunchJobInstanceHandler implements LaunchJobInstanceHandlerInterface
{
    public function __construct(
        private CreateJobExecutionHandlerInterface $createJobExecutionHandler,
        private ExecuteJobExecutionHandlerInterface $executeJobExecutionHandler,
        private JobFileStorerInterface $jobFileStorer,
        private GenerateJobExecutionUrlInterface $generateJobExecutionUrl,
    ) {
    }

    public function handle(LaunchJobInstanceCommand $launchJobInstanceCommand): LaunchJobInstanceResult
    {
        $code = $launchJobInstanceCommand->code;
        $file = $launchJobInstanceCommand->file;

        $filePath = $this->jobFileStorer->store($code, $file->getFileName(), $file->getResource());

        $jobConfig = [
            'storage' => [
                'type' => ManualUploadStorage::TYPE,
                'file_path' => $filePath,
            ],
        ];

        $jobExecution = $this->createJobExecutionHandler->createFromBatchCode($code, $jobConfig, null);
        $this->executeJobExecutionHandler->executeFromJobExecutionId($jobExecution->getId());

        return new LaunchJobInstanceResult(
            $jobExecution->getId(),
            $this->generateJobExecutionUrl->fromJobExecutionId($jobExecution->getId()),
        );
    }
}
