<?php

namespace Akeneo\Platform\Job\Infrastructure\FileStorage;

use Akeneo\Platform\Job\Application\LaunchJobInstance\JobFileStorerInterface;
use Akeneo\Tool\Component\Connector\Job\JobFileLocation;
use League\Flysystem\FilesystemOperator;

class JobFileStorer implements JobFileStorerInterface
{
    public function __construct(
        private FilesystemOperator $filesystem
    ) {
    }

    public function store(string $jobCode, string $fileName, $fileStream): string
    {
        $jobFileLocation = new JobFileLocation($jobCode.DIRECTORY_SEPARATOR.$fileName, true);

        if ($this->filesystem->fileExists($jobFileLocation->path())) {
            $this->filesystem->delete($jobFileLocation->path());
        }

        $this->filesystem->writeStream($jobFileLocation->path(), $fileStream);

        return $jobFileLocation->path();
    }
}
