<?php

namespace Akeneo\Platform\Job\Domain;

interface JobFileStorerInterface
{
    /**
     * @param resource $fileStream
     */
    public function store(string $jobCode, string $fileName, $fileStream): string;
}
