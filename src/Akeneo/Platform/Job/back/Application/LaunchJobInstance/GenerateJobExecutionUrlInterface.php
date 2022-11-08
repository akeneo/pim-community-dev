<?php

namespace Akeneo\Platform\Job\Application\LaunchJobInstance;

interface GenerateJobExecutionUrlInterface
{
    public function fromJobExecutionId(int $jobExecutionId): string;
}
