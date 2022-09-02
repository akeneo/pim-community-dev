<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\Value\Measurement;

final class MeasurementUnitNotFoundException extends \RuntimeException implements MeasurementException
{
    private function __construct(private string $unitCode, string $measurementFamilyCode)
    {
        parent::__construct(
            \sprintf('The "%s" unit does not exist in the "%s" measurement family', $unitCode, $measurementFamilyCode)
        );
    }

    public static function forUnit(string $unitCode, string $measurementFamilyCode): self
    {
        return new self($unitCode, $measurementFamilyCode);
    }

    public function errorField(): string
    {
        return 'unit';
    }

    public function errorValue(): string
    {
        return $this->unitCode;
    }
}
