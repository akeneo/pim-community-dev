<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Model\StepExecution;

interface JobStopperInterface
{
    public function isStopping(StepExecution $stepExecution): bool;

    public function stop(StepExecution $stepExecution): void;

    public function isPausing(StepExecution $stepExecution): bool;

    public function pause(StepExecution $stepExecution, array $currentState): void;
}
