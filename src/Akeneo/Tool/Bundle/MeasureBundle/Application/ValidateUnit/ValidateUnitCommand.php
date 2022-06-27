<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Application\ValidateUnit;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValidateUnitCommand
{
    public string $measurementFamilyCode;

    public string $code;

    public array $labels;

    public array $convert_from_standard;

    public string $symbol;
}
