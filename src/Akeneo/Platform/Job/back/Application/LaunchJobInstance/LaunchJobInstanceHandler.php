<?php

namespace Akeneo\Platform\Job\Application\LaunchJobInstance;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Job\Domain\JobFileStorerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceHandlerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LaunchJobInstanceHandler implements LaunchJobInstanceHandlerInterface
{
    public function __construct(
        private JobFileStorerInterface $jobFileStorer,
        private GenerateJobExecutionUrlInterface $generateJobExecutionUrl,
        private JobLauncherInterface $jobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function handle(LaunchJobInstanceCommand $launchJobInstanceCommand): LaunchJobInstanceResult
    {
        $code = $launchJobInstanceCommand->code;
        $file = $launchJobInstanceCommand->file;

        $filePath = $this->jobFileStorer->store($code, $file->getFileName(), $file->getResource());
        $storageConfig = [
            'storage' => [
                'type' => ManualUploadStorage::TYPE,
                'file_path' => $filePath,
            ],
        ];
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($code);
        $defaultJobConfig = $jobInstance->getRawParameters();

        $user = $this->tokenStorage->getToken()?->getUser();

        $jobExecution = $this->jobLauncher->launch($jobInstance, $user, array_merge($defaultJobConfig, $storageConfig));

        return new LaunchJobInstanceResult(
            $jobExecution->getId(),
            $this->generateJobExecutionUrl->fromJobExecutionId($jobExecution->getId()),
        );
    }
}
