<?php

namespace Akeneo\Platform\Job\Application\LaunchJobInstance;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\ManualUploadStorage;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceHandlerInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\LaunchJobInstanceResult;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LaunchJobInstanceHandler implements LaunchJobInstanceHandlerInterface
{
    public function __construct(
        private readonly JobFileStorerInterface $jobFileStorer,
        private readonly GenerateJobExecutionUrlInterface $generateJobExecutionUrl,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PublishJobToQueueInterface $publishJobToQueue,
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
            'is_user_authenticated' => true,
        ];

        $username = $this->tokenStorage->getToken()?->getUser()?->getUserIdentifier();

        $jobExecution = $this->publishJobToQueue->publish(
            jobInstanceCode: $code,
            config: $jobConfig,
            username: $username,
        );

        return new LaunchJobInstanceResult(
            $jobExecution->getId(),
            $this->generateJobExecutionUrl->fromJobExecutionId($jobExecution->getId()),
        );
    }
}
