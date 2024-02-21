<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Convert;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnknownOperatorException;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;

/**
 * Aims to convert measures
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MeasureConverter
{
    public const SCALE = 12;

    private ?string $family = null;
    private LegacyMeasurementProvider $legacyMeasurementProvider;

    public function __construct(LegacyMeasurementProvider $provider)
    {
        $this->legacyMeasurementProvider = $provider;
    }

    /**
     * Set a family for the converter
     *
     * @throws MeasurementFamilyNotFoundException
     */
    public function setFamily($familyCode): MeasureConverter
    {
        $measurementFamilies = $this->legacyMeasurementProvider->getMeasurementFamilies();
        if (isset($measurementFamilies[$familyCode])) {
            $this->family = $familyCode;

            return $this;
        }

        foreach (\array_keys($measurementFamilies) as $measurementFamilyCode) {
            if (\strtolower($measurementFamilyCode) === \strtolower($familyCode)) {
                $this->family = $measurementFamilyCode;

                return $this;
            }
        }

        throw new MeasurementFamilyNotFoundException();
    }

    /**
     * Convert a value from a base measure to a final measure
     *
     * @param string $baseUnit  Base unit for value
     * @param string $finalUnit Result unit for value
     * @param int|float|string $value  Value to convert
     *
     * @return string
     */
    public function convert($baseUnit, $finalUnit, $value)
    {
        $standardValue = $this->convertBaseToStandard($baseUnit, $value);

        return $this->convertStandardToResult($finalUnit, $standardValue);
    }

    /**
     * Convert a value in a base unit to the standard unit
     *
     * @param string $baseUnit Base unit for value
     * @param int|float|string $value Value to convert
     *
     * @return string
     *
     * @throws UnitNotFoundException
     * @throws UnknownOperatorException
     */
    public function convertBaseToStandard($baseUnit, $value)
    {
        $unitInfo = $this->getUnitInfo($baseUnit);
        $conversionConfig = $unitInfo['convert'];
        $convertedValue = $value;

        foreach ($conversionConfig as $operation) {
            foreach ($operation as $operator => $operand) {
                $convertedValue = $this->applyOperation($convertedValue, $operator, $operand);
            }
        }

        return $convertedValue;
    }

    /**
     * Apply operation between value and operand by using operator
     *
     * @param int|float|string $value Value to convert
     * @param string $operator Operator to apply
     * @param string $operand  Operand to use
     *
     * @return string
     *@throws UnknownOperatorException
     */
    protected function applyOperation($value, $operator, $operand)
    {
        if (!is_numeric($value) || (is_string($value) && str_contains($value, ' '))) {
            return '0';
        }

        $processedValue = is_float($value) ? \number_format($value, static::SCALE, '.', '') : (string) $value;

        switch ($operator) {
            case "div":
                if ($operand !== '0') {
                    $processedValue = bcdiv($processedValue, $operand, static::SCALE);
                }
                break;
            case "mul":
                $processedValue = bcmul($processedValue, $operand, static::SCALE);
                break;
            case "add":
                $processedValue = bcadd($processedValue, $operand, static::SCALE);
                break;
            case "sub":
                $processedValue = bcsub($processedValue, $operand, static::SCALE);
                break;
            default:
                throw new UnknownOperatorException();
        }

        return $processedValue;
    }

    /**
     * Convert a value in a standard unit to a final unit
     *
     * @param string $finalUnit Final unit for value
     * @param int|float|string $value  Value to convert
     *
     * @throws UnknownOperatorException
     * @throws UnitNotFoundException
     * @return string
     *
     */
    public function convertStandardToResult($finalUnit, $value)
    {
        $unitInfo = $this->getUnitInfo($finalUnit);
        $conversionConfig = $unitInfo['convert'];
        $convertedValue = $value;

        // calculate result with conversion config (calculs must be reversed and operation inversed)
        foreach (array_reverse($conversionConfig) as $operation) {
            foreach ($operation as $operator => $operand) {
                $convertedValue = $this->applyReversedOperation($convertedValue, $operator, $operand);
            }
        }

        return $convertedValue;
    }

    /**
     * Apply reversed operation between value and operand by using operator
     *
     * @param int|float|string $value Value to convert
     * @param string $operator Operator to apply
     * @param string $operand  Operand to use
     *
     * @return string
     * @throws UnknownOperatorException
     */
    protected function applyReversedOperation($value, $operator, $operand)
    {
        $processedValue = (string) $value;

        switch ($operator) {
            case "div":
                $processedValue = bcmul($processedValue, $operand, static::SCALE);
                break;
            case "mul":
                if ($operand !== '0') {
                    $processedValue = bcdiv($processedValue, $operand, static::SCALE);
                }
                break;
            case "add":
                $processedValue = bcsub($processedValue, $operand, static::SCALE);
                break;
            case "sub":
                $processedValue = bcadd($processedValue, $operand, static::SCALE);
                break;
            default:
                throw new UnknownOperatorException();
        }

        return $processedValue;
    }

    /**
     * @throws UnitNotFoundException
     */
    private function getUnitInfo(string $unitCode): array
    {
        $measurementFamilies = $this->legacyMeasurementProvider->getMeasurementFamilies();
        if (isset($measurementFamilies[$this->family]['units'][$unitCode])) {
            return $measurementFamilies[$this->family]['units'][$unitCode];
        }

        foreach ($measurementFamilies[$this->family]['units'] as $familyUnitCode => $unitInfo) {
            if (\strtolower($familyUnitCode) === \strtolower($unitCode)) {
                return $unitInfo;
            }
        }

        throw new UnitNotFoundException(\sprintf(
            'Could not find metric unit "%s" in family "%s"',
            $unitCode,
            $this->family
        ));
    }
}
