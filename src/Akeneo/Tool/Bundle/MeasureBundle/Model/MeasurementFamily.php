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

    /** @var LabelCollection */
    private $labels;

    /** @var UnitCode */
    private $standardUnitCode;

    /** @var array */
    private $units;

    private function __construct(MeasurementFamilyCode $code, LabelCollection $labels, UnitCode $standardUnitCode, array $units)
    {
        Assert::stringNotEmpty($code);
        Assert::allIsInstanceOf($units, Unit::class);
        Assert::minCount($units, 1);
        $this->assertStandardUnitIsAlsoAUnit($standardUnitCode, $units);
        $this->assertNoDuplicatedUnits($units);

        $this->code = $code;
        $this->labels = $labels;
        $this->standardUnitCode = $standardUnitCode;
        $this->units = $units;
    }

    public static function create(MeasurementFamilyCode $code, LabelCollection $labels, UnitCode $standardUnitCode, array $units): self
    {
        return new self($code,  $labels, $standardUnitCode, $units);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labels->normalize(),
            'standard_unit_code' => $this->standardUnitCode->normalize(),
            'units' => array_map(
                function (Unit $unit) {
                    return $unit->normalize();
                },
                $this->units
            )
        ];
    }

    private function assertStandardUnitIsAlsoAUnit(UnitCode $standardUnitCode, array $units): void
    {
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
            sprintf(
                'Standard unit "%s" has not been found as a unit for this measurement family.',
                $standardUnitCode->normalize()
            )
        );
    }

    private function assertNoDuplicatedUnits(array $units): void
    {
        $normalizedUnitCodes = array_map(
            function (Unit $unit) {
                return $unit->code()->normalize();
            },
            $units
        );
        Assert::uniqueValues($normalizedUnitCodes);
    }
}
