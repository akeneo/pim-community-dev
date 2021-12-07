<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionRowTracking
{
    public function __construct(
        private int $currentStep,
        private int $totalStep,
        /** @var StepExecutionRowTracking[] */
        private array $steps
    ) {
    }

    public function getErrorCount(): int
    {
        return array_reduce(
            $this->steps,
            static fn (int $count, StepExecutionRowTracking $step) => $count + $step->getErrorCount(),
            0,
        );
    }

    public function normalize(): array
    {
        return [
            'current_step' => $this->currentStep,
            'total_step' => $this->totalStep,
            'steps' => array_map(static fn (StepExecutionRowTracking $step) => $step->normalize(), $this->steps),
        ];
    }
}
