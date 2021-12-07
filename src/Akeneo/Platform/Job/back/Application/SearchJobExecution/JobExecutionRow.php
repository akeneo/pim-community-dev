<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Domain\Model\Status;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionRow
{
    public function __construct(
        private int $jobExecutionId,
        private string $jobName,
        private string $type,
        private ?\DateTimeImmutable $startedAt,
        private ?string $username,
        private Status $status,
        private int $warningCount,
        private int $errorCount,
        private bool $isStoppable,
        private JobExecutionRowTracking $tracking
    ) {
    }

    public function normalize(): array
    {
        return [
            'job_execution_id' => $this->jobExecutionId,
            'job_name' => $this->jobName,
            'type' => $this->type,
            'started_at' => $this->startedAt ? $this->startedAt->format(DATE_ATOM) : null,
            'username' => $this->username,
            'status' => $this->status->getLabel(),
            'warning_count' => $this->warningCount,
            'error_count' => $this->errorCount,
            'tracking' => $this->tracking->normalize(),
            'is_stoppable' => $this->isStoppable,
        ];
    }
}
