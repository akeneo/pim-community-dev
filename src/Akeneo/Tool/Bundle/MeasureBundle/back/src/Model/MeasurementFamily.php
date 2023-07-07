<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasurementFamily
{
    public const MIN_UNIT_COUNT = 1;

    private MeasurementFamilyCode $code;

    private LabelCollection $labels;

    private UnitCode $standardUnitCode;

    private array $units;

    private function __construct(MeasurementFamilyCode $code, LabelCollection $labels, UnitCode $standardUnitCode, array $units)
    {
        Assert::allIsInstanceOf($units, Unit::class);
        Assert::minCount($units, self::MIN_UNIT_COUNT);
        $this->assertStandardUnitExists($standardUnitCode, $units);
        $this->assertStandardUnitOperationIsAMultiplyByOne($standardUnitCode, $units);
        $this->assertNoDuplicatedUnits($units);

        $this->code = $code;
        $this->labels = $labels;
        $this->standardUnitCode = $standardUnitCode;
        $this->units = $units;
    }

    public static function create(MeasurementFamilyCode $code, LabelCollection $labels, UnitCode $standardUnitCode, array $units): self
    {
        return new self($code, $labels, $standardUnitCode, $units);
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labels->normalize(),
            'standard_unit_code' => $this->standardUnitCode->normalize(),
            'units' => array_map(
                static fn (Unit $unit) => $unit->normalize(),
                $this->units
            )
        ];
    }

    public function normalizeWithIndexedUnits(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labels->normalize(),
            'standard_unit_code' => $this->standardUnitCode->normalize(),
            'units' => array_reduce($this->units, function (array $units, Unit $unit) {
                $normalizedUnit = $unit->normalize();
                $units[$normalizedUnit['code']] = $normalizedUnit;
                return $units;
            }, []),
        ];
    }

    public function getUnitLabel(UnitCode $unitCode, LocaleIdentifier $localeIdentifier): string
    {
        $unit = $this->getUnit($unitCode, $this->units);

        if (!$unit instanceof Unit) {
            throw new UnitNotFoundException();
        }

        return $unit->getLabel($localeIdentifier);
    }

    private function assertStandardUnitExists(UnitCode $standardUnitCode, array $units): void
    {
        $isStandardUnitCodePresentInUnits = !empty($this->getUnit($standardUnitCode, $units));
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
            static fn (Unit $unit) => $unit->code()->normalize(),
            $units
        );
        Assert::uniqueValues($normalizedUnitCodes);
    }

    private function getUnit(UnitCode $standardUnitCode, array $units): ?Unit
    {
        $unit = current(array_filter(
            $units,
            static fn (Unit $unit) => $standardUnitCode->equals($unit->code())
        ));

        if (!$unit) {
            return null;
        }

        return $unit;
    }

    private function assertStandardUnitOperationIsAMultiplyByOne(UnitCode $standardUnitCode, array $units): void
    {
        $unit = $this->getUnit($standardUnitCode, $units);

        Assert::notNull($unit, sprintf('Standard unit "%s" cannot be found', $standardUnitCode->normalize()));
        Assert::true($unit->canBeAStandardUnit(), sprintf('Standard unit "%s" cannot be a standard unit', $unit->code()->normalize()));
    }
}
