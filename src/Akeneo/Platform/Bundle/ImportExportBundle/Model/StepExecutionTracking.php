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
    public $name;

    /** @var bool */
    public $isTrackable;

    /** @var string */
    public $status;

    /** @var boolean */
    public $hasWarning;

    /** @var boolean */
    public $hasError;

    /** @var int */
    public $duration;

    /** @var int */
    public $processedItems = 0;

    /** @var int */
    public $totalItems = 0;
}
