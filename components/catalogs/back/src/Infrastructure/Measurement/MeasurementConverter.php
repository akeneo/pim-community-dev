<?php

namespace Akeneo\Catalogs\Infrastructure\Measurement;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Measurement\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Catalogs\Infrastructure\Measurement\Exception\OperationsOfThisUnitNotFoundException;
use Akeneo\Catalogs\Infrastructure\Measurement\Exception\UseOfUnknownOperatorException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawMeasurementFamily from GetMeasurementsFamilyQueryInterface
 * @phpstan-import-type RawMeasurementOperation from GetMeasurementsFamilyQueryInterface
 */
final class MeasurementConverter
{
    public const SCALE = 12;

    public function __construct(readonly private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery)
    {
    }

    /**
     * @throws OperationsOfThisUnitNotFoundException
     * @throws UseOfUnknownOperatorException
     * @throws MeasurementFamilyNotFoundException
     */
    public function convert(string $measurementFamilyCode, string $targetedUnit, string $initialUnit, int|float|string $initialAmount): int|float
    {
        $measurementFamily = $this->getMeasurementsFamilyQuery->execute($measurementFamilyCode);

        if (null === $measurementFamily) {
            throw new MeasurementFamilyNotFoundException($measurementFamilyCode);
        }

        $amountConvertedInDefaultUnit = $this->convertAmountToDefaultMeasurementFamilyUnit($measurementFamily, $initialUnit, $initialAmount);

        $amountConvertedInTargetedUnit = $this->convertFromDefaultMeasurementFamilyUnitToTargetedUnit($measurementFamily, $targetedUnit, $amountConvertedInDefaultUnit);

        if (\is_string($amountConvertedInTargetedUnit)) {
            $amountConvertedInTargetedUnit = (float) $amountConvertedInTargetedUnit;
        }

        return $amountConvertedInTargetedUnit;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     * @throws OperationsOfThisUnitNotFoundException
     * @throws UseOfUnknownOperatorException
     */
    private function convertAmountToDefaultMeasurementFamilyUnit(array $measurementFamily, string $initialUnit, int|float|string $initialAmount): int|float|string
    {
        $operations = $this->getUnitOperations($measurementFamily, $initialUnit);

        $toStandardAmount = $initialAmount;
        foreach ($operations as $operation) {
            $toStandardAmount = $this->applyOperation($toStandardAmount, $operation['operator'], $operation['value']);
        }

        return $toStandardAmount;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     * @throws OperationsOfThisUnitNotFoundException
     * @throws UseOfUnknownOperatorException
     */
    private function convertFromDefaultMeasurementFamilyUnitToTargetedUnit(array $measurementFamily, string $targetedUnit, int|float|string $standardAmount): int|float|string
    {
        $operations = $this->getUnitOperations($measurementFamily, $targetedUnit);

        $toTargetedUnitAmount = $standardAmount;

        foreach (\array_reverse($operations) as $operation) {
            $toTargetedUnitAmount = $this->applyReversedOperation($toTargetedUnitAmount, $operation['operator'], $operation['value']);
        }

        return $toTargetedUnitAmount;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     * @return array<RawMeasurementOperation>
     *
     * @throws OperationsOfThisUnitNotFoundException
     */
    private function getUnitOperations(array $measurementFamily, string $unitCode): array
    {
        foreach ($measurementFamily['units'] as $unit) {
            if ($unit['code'] === $unitCode) {
                return $unit['convert_from_standard'];
            }
        }

        throw new OperationsOfThisUnitNotFoundException($unitCode, $measurementFamily['code']);
    }

    private function applyOperation(int|float|string $amount, string $operator, float|int $operand): string
    {
        if (!\is_numeric($amount)) {
            return '0';
        }
        /** @var numeric-string $processedAmount */
        $processedAmount = \is_float($amount) ? \number_format($amount, MeasurementConverter::SCALE, '.', '') : (string) $amount;
        /** @var numeric-string $operand */
        $operand = \number_format($operand, MeasurementConverter::SCALE, '.', '');

        switch ($operator) {
            case 'div':
                if ($operand != 0) {
                    $processedAmount = \bcdiv($processedAmount, $operand, MeasurementConverter::SCALE);
                }
                break;
            case 'mul':
                $processedAmount = \bcmul($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case 'add':
                $processedAmount = \bcadd($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case 'sub':
                $processedAmount = \bcsub($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            default:
                throw new UseOfUnknownOperatorException($operator);
        }

        return $processedAmount;
    }

    private function applyReversedOperation(int|float|string $value, string $operator, float|int $operand): string
    {
        /** @var numeric-string $processedAmount */
        $processedAmount = (string) $value;
        /** @var numeric-string $operand */
        $operand = \number_format($operand, MeasurementConverter::SCALE, '.', '');

        switch ($operator) {
            case 'div':
                $processedAmount = \bcmul($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case 'mul':
                if ($operand != 0) {
                    $processedAmount = \bcdiv($processedAmount, $operand, MeasurementConverter::SCALE);
                }
                break;
            case 'add':
                $processedAmount = \bcsub($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            case 'sub':
                $processedAmount = \bcadd($processedAmount, $operand, MeasurementConverter::SCALE);
                break;
            default:
                throw new UseOfUnknownOperatorException($operator);
        }

        return $processedAmount;
    }
}
