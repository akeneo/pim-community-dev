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

namespace Akeneo\Platform\TailoredExport\Application\Common\Operation;

class MeasurementConversionOperation implements OperationInterface
{
    public function __construct(
        private string $measurementFamilyCode,
        private string $targetUnitCode,
    ) {
    }

    public function getMeasurementFamilyCode(): string
    {
        return $this->measurementFamilyCode;
    }

    public function getTargetUnitCode(): string
    {
        return $this->targetUnitCode;
    }
}
