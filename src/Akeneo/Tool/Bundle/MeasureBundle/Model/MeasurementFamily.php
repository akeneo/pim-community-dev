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
    private $standardUnitCode;

    /** @var array */
    private $units;

    private function __construct(MeasurementFamilyCode $code, UnitCode $standardUnitCode, array $units)
    {
        Assert::allIsInstanceOf($units, Unit::class);
        Assert::minCount($units, 1);
        $isStandardUnitCodePresentInUnits = !empty(
            array_filter(
                $units,
                function (Unit $unit) use ($standardUnitCode) {
                    return $standardUnitCode->equals($unit->code());
                }
            )
        );
        Assert::true(
            $isStandardUnitCodePresentInUnits,
            sprintf('Standard unit "%s" has not been found as a unit for this measurement family.', $standardUnitCode->normalize())
        );
        // Check there is no duplication of units in the array

        $this->code = $code;
        $this->standardUnitCode = $standardUnitCode;
        $this->units = $units;
    }

    public static function create(MeasurementFamilyCode $code, UnitCode $standardUnitCode, array $units): self
    {
        return new self($code, $standardUnitCode, $units);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'standard_unit_code' => $this->standardUnitCode->normalize(),
            'units' => array_map(
                function (Unit $unit) {
                    return $unit->normalize();
                },
                $this->units
            )
        ];
    }
}
