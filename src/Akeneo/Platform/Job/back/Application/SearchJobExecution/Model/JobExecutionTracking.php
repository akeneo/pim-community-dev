<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionTracking
{
    public function __construct(
        private int $currentStep,
        private int $totalStep,
        /** @var StepExecutionTracking[] */
        private array $steps,
    ) {
        Assert::allIsInstanceOf($steps, StepExecutionTracking::class);
    }

    public function hasError(): bool
    {
        return !empty(array_filter(
            $this->steps,
            static fn (StepExecutionTracking $step) => $step->hasError()
        ));
    }

    public function getWarningCount(): int
    {
        return array_reduce(
            $this->steps,
            static fn (int $count, StepExecutionTracking $step) => $count + $step->getWarningCount(),
            0,
        );
    }

    public function normalize(): array
    {
        return [
            'current_step' => $this->currentStep,
            'total_step' => $this->totalStep,
            'steps' => array_map(static fn (StepExecutionTracking $step) => $step->normalize(), $this->steps),
        ];
    }
}
