<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

interface MarkJobExecutionAsFailedWhenInterrupted
{
    public function execute(array $jobCodes): int;
}
