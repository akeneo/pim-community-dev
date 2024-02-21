<?php

namespace Akeneo\Platform\Job\Test\Acceptance\FakeServices;

use Akeneo\Platform\Job\Application\LaunchJobInstance\JobFileStorerInterface;

class InMemoryJobFileStorer implements JobFileStorerInterface
{
    public function store(string $jobCode, string $fileName, $fileStream): string
    {
        return sprintf('%s/%s', $jobCode, $fileName);
    }
}
