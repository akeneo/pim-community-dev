<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;

interface GetJobExecutionStatusInterface
{
    public function getByJobExecutionId(int $jobExecutionId): ?BatchStatus;
}
