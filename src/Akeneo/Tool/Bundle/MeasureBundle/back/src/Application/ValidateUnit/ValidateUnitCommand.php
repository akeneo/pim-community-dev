<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Application\ValidateUnit;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValidateUnitCommand
{
    /** @var string */
    public $measurementFamilyCode;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var array */
    public $convert_from_standard;

    /** @var string */
    public $symbol;
}
