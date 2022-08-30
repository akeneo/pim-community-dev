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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementUnitExists;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnit;
use Webmozart\Assert\Assert;

final class ACLMeasurementUnitExists implements MeasurementUnitExists
{
    public function __construct(private ?GetUnit $getUnit)
    {
    }

    public function inFamily(string $measurementFamilyCode, string $measurementUnitCode): bool
    {
        Assert::notNull($this->getUnit);
        try {
            $this->getUnit->byMeasurementFamilyCodeAndUnitCode($measurementFamilyCode, $measurementUnitCode);
        } catch (\Exception) {
            return false;
        }

        return true;
    }
}
