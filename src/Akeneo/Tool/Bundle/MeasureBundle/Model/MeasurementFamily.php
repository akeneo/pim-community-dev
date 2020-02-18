<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamily
{
    /** @var string */
    private $code;

    /** @var UnitCode */
    private $standardUnit;

    /** @var array */
    private $units;

    private function __construct(MeasurementFamilyCode $code, UnitCode $standardUnit, array $units)
    {
        Assert::allIsInstanceOf($units, Unit::class);
        Assert::minCount($units, 1);

        // Check standard unit is available in the units
        // Check there is no duplication of units in the array

        $this->code = $code;
        $this->standardUnit = $standardUnit;
        $this->units = $units;
    }

    public static function create(MeasurementFamilyCode $code, UnitCode $standardUnit, array $units): self
    {
        return new self($code, $standardUnit, $units);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'standard_unit' => $this->standardUnit->normalize(),
            'units' => array_map(
                function (Unit $unit) {
                    return $unit->normalize();
                },
                $this->units
            )
        ];
    }
}
