<?php

namespace Akeneo\Platform\Job\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionHealthCheck;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Infrastructure\Clock\ClockInterface;

class JobExecutionHealthCheckHydrator
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function hydrate(int $status, ?string $healthCheckTime): JobExecutionHealthCheck
    {
        $healthCheckedAt = null !== $healthCheckTime ?
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $healthCheckTime, new \DateTimeZone('UTC'))
            : null;

        $currentTime = $this->clock->now();

        return new JobExecutionHealthCheck(
            Status::fromStatus($status),
            $healthCheckedAt,
            $currentTime,
        );
    }
}
