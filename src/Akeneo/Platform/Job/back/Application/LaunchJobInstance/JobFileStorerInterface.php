<?php

namespace Akeneo\Platform\Job\Application\LaunchJobInstance;

interface JobFileStorerInterface
{
    /**
     * @param resource $fileStream
     */
    public function store(string $jobCode, string $fileName, $fileStream): string;
}
