<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Measurement;

use Akeneo\Platform\TailoredExport\Domain\Query\FindUnitSymbolInterface;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;

class FindUnitSymbol implements FindUnitSymbolInterface
{
    public function __construct(
        private GetUnit $getUnit,
    ) {
    }

    public function byFamilyCodeAndUnitCode(string $familyCode, string $unitCode): ?string
    {
        $unit = $this->getUnit->byMeasurementFamilyCodeAndUnitCode(
            $familyCode,
            $unitCode,
        );

        return $unit->symbol ?? null;
    }
}
