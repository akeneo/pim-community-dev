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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementFamilyExists;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Webmozart\Assert\Assert;

final class ACLMeasurementFamilyExists implements MeasurementFamilyExists
{
    public function __construct(private ?MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
    }

    public function forCode(string $code): bool
    {
        Assert::notNull($this->measurementFamilyRepository);
        try {
            $code = MeasurementFamilyCode::fromString($code);
        } catch (\InvalidArgumentException) {
            return false;
        }

        try {
            $this->measurementFamilyRepository->getByCode($code);
        } catch (MeasurementFamilyNotFoundException) {
            return false;
        }

        return true;
    }
}
