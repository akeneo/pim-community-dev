<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Measurement\MeasureConverter;
use Akeneo\Pim\TableAttribute\Domain\Value\Measurement\MeasurementUnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter as ForeignMeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Webmozart\Assert\Assert;

class AclMeasureConverter implements MeasureConverter
{
    public function __construct(private ?ForeignMeasureConverter $measureConverter)
    {
    }

    public function convertAmountInStandardUnit(
        MeasurementFamilyCode $measurementFamilyCode,
        string $amount,
        string $unit
    ): string {
        Assert::notNull($this->measureConverter);
        Assert::numeric($amount);

        $this->measureConverter->setFamily($measurementFamilyCode->asString());
        try {
            return (string)$this->measureConverter->convertBaseToStandard($unit, $amount);
        } catch (UnitNotFoundException) {
            throw MeasurementUnitNotFoundException::forUnit($unit, $measurementFamilyCode->asString());
        }
    }
}
