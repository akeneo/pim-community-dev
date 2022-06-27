<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SaveMeasurementFamilyCommand
{
    public string $code;

    public array $labels;

    public string $standardUnitCode;

    public array $units;
}
