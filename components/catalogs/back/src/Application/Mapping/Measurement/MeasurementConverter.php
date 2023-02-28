<?php

namespace Akeneo\Catalogs\Application\Mapping\Measurement;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawMeasurementFamily from GetMeasurementsFamilyQueryInterface
 * @phpstan-import-type RawMeasurementOperation from GetMeasurementsFamilyQueryInterface
 */
final class MeasurementConverter
{
    public const DECIMAL_NUMBER = 12;

    public function __construct(readonly private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery)
    {
    }

    public function convert(string $measurementFamilyCode, string $targetedUnit, string $initialUnit, int|float|string $amount): int|float
    {
        $measurementFamily = $this->getMeasurementsFamilyQuery->execute($measurementFamilyCode);

        if (null === $measurementFamily) {
            throw new \LogicException(\sprintf(
                'The measurement family with this code : %s have not been found.',
                $measurementFamilyCode,
            ));
        }

        $amount = $this->convertAmountToDefaultMeasurementFamilyUnit($measurementFamily, $initialUnit, $amount);

        $amount = $this->convertFromDefaultMeasurementFamilyUnitToTargetedUnit($measurementFamily, $targetedUnit, $amount);

        if (\is_string($amount)) {
            $amount = (float) $amount;
        }

        return $amount;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     */
    private function convertAmountToDefaultMeasurementFamilyUnit(array $measurementFamily, string $initialUnit, int|float|string $amount): int|float|string
    {
        $operations = $this->getUnitOperations($measurementFamily, $initialUnit);

        $toStandardAmount = $amount;
        foreach ($operations as $operation) {
            $toStandardAmount = $this->applyOperation($toStandardAmount, $operation['operator'], $operation['value']);
        }

        return $toStandardAmount;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     */
    private function convertFromDefaultMeasurementFamilyUnitToTargetedUnit(array $measurementFamily, string $targetedUnit, int|float|string $amount): int|float|string
    {
        $operations = $this->getUnitOperations($measurementFamily, $targetedUnit);

        $toTargetedUnitAmount = $amount;

        foreach (\array_reverse($operations) as $operation) {
            $toTargetedUnitAmount = $this->applyReversedOperation($toTargetedUnitAmount, $operation['operator'], $operation['value']);
        }

        return $toTargetedUnitAmount;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     * @return array<RawMeasurementOperation>
     */
    private function getUnitOperations(array $measurementFamily, string $unitCode): array
    {
        foreach ($measurementFamily['units'] as $unit) {
            if ($unit['code'] === $unitCode) {
                return $unit['convert_from_standard'];
            }
        }

        throw new \LogicException(\sprintf(
            'The Operations of this unit : %s of the measurement family : %s have not been found.',
            $unitCode,
            $measurementFamily['code'],
        ));
    }

    private function applyOperation(int|float|string $amount, string $operator, float|int $operand): string
    {
        if (!\is_numeric($amount)) {
            throw new \Exception('The value of amount must be a numeric value.');
        }
        /** @var numeric-string $processedAmount */
        $processedAmount = \is_float($amount) ? \number_format($amount, self::DECIMAL_NUMBER, '.', '') : (string) $amount;
        /** @var numeric-string $operand */
        $operand = \number_format($operand, self::DECIMAL_NUMBER, '.', '');

        switch ($operator) {
            case 'div':
                if ($operand != 0) {
                    $processedAmount = \bcdiv($processedAmount, $operand, self::DECIMAL_NUMBER);
                }
                break;
            case 'mul':
                $processedAmount = \bcmul($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            case 'add':
                $processedAmount = \bcadd($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            case 'sub':
                $processedAmount = \bcsub($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            default:
                throw new \LogicException(\sprintf(
                    'The operator : %s used for this operation is not listed in the configured operator for this measurement unit.',
                    $operator,
                ));
        }

        return $processedAmount;
    }

    private function applyReversedOperation(int|float|string $value, string $operator, float|int $operand): string
    {
        /** @var numeric-string $processedAmount */
        $processedAmount = (string) $value;
        /** @var numeric-string $operand */
        $operand = \number_format($operand, self::DECIMAL_NUMBER, '.', '');

        switch ($operator) {
            case 'div':
                $processedAmount = \bcmul($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            case 'mul':
                if ($operand != 0) {
                    $processedAmount = \bcdiv($processedAmount, $operand, self::DECIMAL_NUMBER);
                }
                break;
            case 'add':
                $processedAmount = \bcsub($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            case 'sub':
                $processedAmount = \bcadd($processedAmount, $operand, self::DECIMAL_NUMBER);
                break;
            default:
                throw new \LogicException(\sprintf(
                    'The operator : %s used for this operation is not listed in the configured operator for this measurement unit.',
                    $operator,
                ));
        }

        return $processedAmount;
    }
}
