<?php

namespace Akeneo\Catalogs\Infrastructure\Measurement;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Measurement\Exception\OperationsOfThisUnitNotFoundException;
use Akeneo\Catalogs\Infrastructure\Measurement\Exception\UseOfUnknownOperatorException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MeasurementConverter
{
    public const SCALE = 12;

    public function __construct(readonly private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery)
    {
    }

    public function convert(string $measurementFamilyCode, string $targetedUnit, string $initialUnit, int|float|string $initialAmount)
    {
        $measurementFamily = $this->getMeasurementsFamilyQuery->execute($measurementFamilyCode);
        $amountConvertedInDefaultUnit = $this->convertAmountToDefaultMeasurementFamilyUnit($measurementFamily, $initialUnit, $initialAmount);
        return (float) $this->convertFromDefaultMeasurementFamilyUnitToTargetedUnit($measurementFamily, $targetedUnit, $amountConvertedInDefaultUnit);
    }

    private function convertAmountToDefaultMeasurementFamilyUnit($measurementFamily, string $initialUnit, int|float $initialAmount): float|int
    {
        $operations = $this->retrieveOperationsOfThisUnit($measurementFamily, $initialUnit);

        $toStandardAmount = $initialAmount;
        foreach ($operations as $operation) {
            $toStandardAmount = $this->applyOperation($toStandardAmount, $operation['operator'], $operation['value']);
        }
        return $toStandardAmount;
    }

    private function convertFromDefaultMeasurementFamilyUnitToTargetedUnit($measurementFamily, string $targetedUnit, int|float $standardAmount): float|int
    {
        $operations = $this->retrieveOperationsOfThisUnit($measurementFamily, $targetedUnit);

        $toTargetedUnitAmount = $standardAmount;
        foreach (array_reverse($operations) as $operation) {
            $toTargetedUnitAmount = $this->applyReversedOperation($toTargetedUnitAmount, $operation['operator'], $operation['value']);
        }
        return $toTargetedUnitAmount;
    }
    private function retrieveOperationsOfThisUnit($measurementFamily, $intialOrTargetedUnit)
    {
        $operations = null;
        foreach ($measurementFamily['units'] as $unit) {
            if ($unit['code'] === $intialOrTargetedUnit) {
                return $unit['convert_from_standard'];
            }
        }
        if (null === $operations) {
            throw new OperationsOfThisUnitNotFoundException($intialOrTargetedUnit, $measurementFamily['code']);
        }
    }

    private function applyOperation(int|float $amount, string $operator, string $operand): int|float
    {
        if (!\is_numeric($amount) || (\is_string($amount) && \str_contains($amount, ' '))) {
            return '0';
        }

        $processedAmount = \is_float($amount) ? \number_format($amount, MeasurementConverter::SCALE, '.', '') : (string) $amount;

        switch ($operator) {
            case "div":
                if ($operand !== '0') {
                    $processedAmount = \bcdiv($processedAmount, $operand, MeasurementConverter::SCALE);
                }
                break;
            case "mul":
                $processedAmount = \bcmul($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case "add":
                $processedAmount = \bcadd($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case "sub":
                $processedAmount = \bcsub($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            default:
                throw new UseOfUnknownOperatorException($operator);
        }

        return $processedAmount;
    }

    private function applyReversedOperation($value, $operator, $operand)
    {
        $processedValue = (string) $value;

        switch ($operator) {
            case "div":
                $processedValue = \bcmul($processedValue, $operand, MeasurementConverter::SCALE);
                break;
            case "mul":
                if ($operand !== '0') {
                    $processedValue = \bcdiv($processedValue, $operand, MeasurementConverter::SCALE);
                }
                break;
            case "add":
                $processedValue = \bcsub($processedValue, $operand, MeasurementConverter::SCALE);
                break;
            case "sub":
                $processedValue = \bcadd($processedValue, $operand, MeasurementConverter::SCALE);
                break;
            default:
                throw new UseOfUnknownOperatorException($operator);
        }

        return $processedValue;
    }
}
