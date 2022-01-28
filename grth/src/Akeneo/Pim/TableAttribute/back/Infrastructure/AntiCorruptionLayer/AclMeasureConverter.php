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
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Webmozart\Assert\Assert;

class AclMeasureConverter
{
    public function __construct(private ?MeasureConverter $measureConverter)
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

        return (string) $this->measureConverter->convertBaseToStandard($unit, $amount);
    }
}
