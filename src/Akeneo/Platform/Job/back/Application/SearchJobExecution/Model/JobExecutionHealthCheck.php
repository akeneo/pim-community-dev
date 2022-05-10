<?php

namespace Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Domain\Model\Status;

final class JobExecutionHealthCheck
{
    private const MAX_TIME_TO_UPDATE_HEALTH_CHECK = 5;
    private const HEALTH_CHECK_INTERVAL = 5;

    public function __construct(
        private Status $currentStatus,
        private ?\DateTimeImmutable $healthCheckedAt,
        private \DateTimeImmutable $currentTime,
    ) {
    }

    public function resolveStatus(): Status
    {
        if (null === $this->healthCheckedAt || !$this->hasRunningStatus()) {
            return $this->currentStatus;
        }

        $diffInSeconds = $this->currentTime->getTimestamp() - $this->healthCheckedAt->getTimestamp();

        if ($diffInSeconds > self::HEALTH_CHECK_INTERVAL + self::MAX_TIME_TO_UPDATE_HEALTH_CHECK) {
            return Status::fromStatus(Status::FAILED);
        }

        return $this->currentStatus;
    }

    private function hasRunningStatus(): bool
    {
        return in_array($this->currentStatus->getStatus(), [Status::STARTING, Status::IN_PROGRESS, Status::STOPPING]);
    }
}
