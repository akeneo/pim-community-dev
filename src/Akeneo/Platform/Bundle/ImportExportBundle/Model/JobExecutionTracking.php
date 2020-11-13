<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Model;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionTracking
{
    /** @var string */
    public $status;

    /** @var int */
    public $currentStep;

    /** @var int */
    public $totalSteps;

    /** @var StepExecutionTracking[] */
    public $steps;

    public function hasError(): bool
    {
        return array_reduce(
            $this->steps,
            static function (bool $hasError, StepExecutionTracking $stepExecutionTracking) {
                return $hasError || $stepExecutionTracking->hasError;
            },
            false
        );
    }

    public function hasWarning(): bool
    {
        return array_reduce(
            $this->steps,
            static function (bool $hasWarning, StepExecutionTracking $stepExecutionTracking) {
                return $hasWarning || $stepExecutionTracking->hasWarning;
            },
            false
        );
    }
}
