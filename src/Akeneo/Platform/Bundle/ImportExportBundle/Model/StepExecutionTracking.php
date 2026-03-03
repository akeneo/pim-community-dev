<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Model;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StepExecutionTracking
{
    /** @var string */
    public $jobName;

    /** @var string */
    public $stepName;

    /** @var string */
    public $status;

    /** @var bool */
    public $isTrackable = false;

    /** @var bool */
    public $hasWarning = false;

    /** @var bool */
    public $hasError = false;

    /** @var int */
    public $duration = 0;

    /** @var int */
    public $processedItems = 0;

    /** @var int */
    public $totalItems = 0;
}
