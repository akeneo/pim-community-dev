<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\ServiceApi;

/**
 * @author Adrien Pétremann <adrien.petremann@getakeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindUnit
{
    public function byMeasurementFamilyCodeAndUnitCode(string $measurementFamilyCode, string $unitCode): ?Unit;
}
