<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\PublicApi;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetUnitTranslations
{
    /**
     * Returns an array with unit codes as keys and unit translations as values.
     *
     * @return string[]
     */
    public function byMeasurementFamilyCodeAndLocale(string $measurementFamilyCode, string $localeCode): array;
}
