<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Hydrator;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobExecutionTrackingHydrator
{
    public function __construct(
        private StepExecutionTrackingHydrator $stepExecutionHydrator,
    ) {
    }

    public function hydrate(int $currentStepNumber, int $stepCount, array $steps): JobExecutionTracking
    {
        if (0 === $currentStepNumber) {
            return new JobExecutionTracking(
                $currentStepNumber,
                $stepCount,
                [],
            );
        }

        $steps = array_map(
            fn (array $step) => $this->stepExecutionHydrator->hydrate($step),
            $steps,
        );

        usort(
            $steps,
            static fn (StepExecutionTracking $step1, StepExecutionTracking $step2) => $step1->getId() <=> $step2->getId(),
        );

        return new JobExecutionTracking(
            $currentStepNumber,
            $stepCount,
            $steps,
        );
    }
}
