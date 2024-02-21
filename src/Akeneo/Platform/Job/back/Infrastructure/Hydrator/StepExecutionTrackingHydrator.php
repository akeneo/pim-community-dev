<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use Akeneo\Platform\Job\Infrastructure\Clock\ClockInterface;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class StepExecutionTrackingHydrator
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function hydrate(array $stepExecution): StepExecutionTracking
    {
        $startTime = $stepExecution['start_time'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $stepExecution['start_time']) : null;
        $endTime = $stepExecution['end_time'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $stepExecution['end_time']) : null;
        $status = Status::fromStatus((int) $stepExecution['status']);
        $duration = $this->computeDuration($status, $startTime, $endTime);

        return new StepExecutionTracking(
            (int) $stepExecution['id'],
            $duration,
            (int) $stepExecution['warning_count'],
            (bool) $stepExecution['has_error'],
            (int) $stepExecution['total_items'],
            (int) $stepExecution['processed_items'],
            (bool) $stepExecution['is_trackable'],
            $status,
        );
    }

    private function computeDuration(Status $status, ?\DateTimeImmutable $startTime, ?\DateTimeImmutable $endTime): int
    {
        $now = $this->clock->now();
        if (Status::STARTING === $status->getStatus() || !$startTime instanceof \DateTimeImmutable) {
            return 0;
        }

        $duration = $now->getTimestamp() - $startTime->getTimestamp();
        if ($endTime instanceof \DateTimeImmutable) {
            $duration = $endTime->getTimestamp() - $startTime->getTimestamp();
        }

        return $duration;
    }
}
