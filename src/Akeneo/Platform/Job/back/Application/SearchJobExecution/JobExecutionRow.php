<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionRow
{
    private int $jobExecutionId;
    private string $jobName;
    private string $type;
    private ?string $startedAt;
    private ?string $username;
    private string $status;
    private int $warningCount;

    public function __construct(int $jobExecutionId, string $jobName, string $type, ?string $startedAt, ?string $username, string $status, int $warningCount)
    {
        $this->jobExecutionId = $jobExecutionId;
        $this->jobName = $jobName;
        $this->type = $type;
        $this->startedAt = $startedAt;
        $this->username = $username;
        $this->status = $status;
        $this->warningCount = $warningCount;
    }

    public function normalize(): array
    {
        return [
            'job_execution_id' => $this->jobExecutionId,
            'job_name' => $this->jobName,
            'type' => $this->type,
            'started_at' => $this->startedAt,
            'username' => $this->username,
            'status' => $this->status,
            'warning_count' => $this->warningCount,
        ];
    }
}
