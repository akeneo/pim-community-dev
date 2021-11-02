<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecutionTable;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionRow
{
    private string $jobName;
    private string $type;
    private ?string $startAt;
    private ?string $username;
    private string $status;
    private int $warningCount;

    public function __construct(string $jobName, string $type, ?string $startAt, ?string $username, string $status, int $warningCount)
    {
        $this->jobName = $jobName;
        $this->type = $type;
        $this->startAt = $startAt;
        $this->username = $username;
        $this->status = $status;
        $this->warningCount = $warningCount;
    }

    public function normalize(): array
    {
        return [
            'jobName' => $this->jobName,
            'type' => $this->type,
            'start_at' => $this->startAt,
            'username' => $this->username,
            'status' => $this->status,
            'warning_count' => $this->warningCount,
        ];
    }
}
