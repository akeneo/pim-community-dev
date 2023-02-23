<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Measurement;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetMeasurementsFamilyQueryInterface
{
    /**
     * @return array{code: string, units: array<array{code: string, label: string}>, standard_unit: string}|null
     */
    public function execute(string $code, string $locale = 'en_US'): ?array;
}
