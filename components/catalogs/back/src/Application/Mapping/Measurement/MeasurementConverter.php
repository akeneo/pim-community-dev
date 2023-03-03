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

        $amountInDefaultUnit = $this->convertAmountToDefaultMeasurementFamilyUnit($measurementFamily, $initialUnit, $amount);

        $amountInTargetUnit = $this->convertFromDefaultMeasurementFamilyUnitToTargetedUnit($measurementFamily, $targetedUnit, $amountInDefaultUnit);

        if (\is_string($amountInTargetUnit)) {
            $amountInTargetUnit = (float) $amountInTargetUnit;
        }

        return $amountInTargetUnit;
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     */
    private function convertAmountToDefaultMeasurementFamilyUnit(array $measurementFamily, string $initialUnit, int|float|string $amount): int|float|string
    {
        return \array_reduce(
            $this->getUnitOperations($measurementFamily, $initialUnit),
            function (float|int|string $carry, array $operation): string {
                $operator = (string) $operation['operator'];
                /** @var numeric-string $operand */
                $operand = (string) $operation['value'];
                return $this->applyOperation($carry, $operator, $operand);
            },
            $amount,
        );
    }

    /**
     * @param RawMeasurementFamily $measurementFamily
     */
    private function convertFromDefaultMeasurementFamilyUnitToTargetedUnit(array $measurementFamily, string $targetedUnit, int|float|string $amount): int|float|string
    {
        return \array_reduce(
            \array_reverse($this->getUnitOperations($measurementFamily, $targetedUnit)),
            function (float|int|string $currentAmount, array $operation): string {
                $operator = (string) $operation['operator'];
                /** @var numeric-string $operand */
                $operand = (string) $operation['value'];
                return $this->applyReversedOperation($currentAmount, $operator, $operand);
            },
            $amount,
        );
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

    /**
     * @param numeric-string $operand
     */
    private function applyOperation(int|float|string $amount, string $operator, string $operand): string
    {
        if (!\is_numeric($amount)) {
            throw new \Exception('The value of amount must be a numeric value.');
        }
        /** @var numeric-string $processedAmount */
        $processedAmount = \is_float($amount) ? \number_format($amount, self::DECIMAL_NUMBER, '.', '') : (string) $amount;
        if ($operator === 'div' && $operand == 0) {
            return $processedAmount;
        }
        // Gérer le cas du 0 => renvoi processedAmount
        return match ($operator) {
            'div' => \bcdiv($processedAmount, $operand, self::DECIMAL_NUMBER),
            'mul' => \bcmul($processedAmount, $operand, self::DECIMAL_NUMBER),
            'add' => \bcadd($processedAmount, $operand, self::DECIMAL_NUMBER),
            'sub' => \bcsub($processedAmount, $operand, self::DECIMAL_NUMBER),
            default => throw new \LogicException(\sprintf(
                'The operator : %s used for this operation is not listed in the configured operator for this measurement unit.',
                $operator,
            )),
        };
    }

    /**
     * @param numeric-string $operand
     */
    private function applyReversedOperation(int|float|string $value, string $operator, string $operand): string
    {
        /** @var numeric-string $processedAmount */
        $processedAmount = (string) $value;

        if ($operator === 'div' && $operand == 0) {
            return $processedAmount;
        }

        return match ($operator) {
            'div' => \bcmul($processedAmount, $operand, self::DECIMAL_NUMBER),
            'mul' => \bcdiv($processedAmount, $operand, self::DECIMAL_NUMBER),
            'add' => \bcsub($processedAmount, $operand, self::DECIMAL_NUMBER),
            'sub' => \bcadd($processedAmount, $operand, self::DECIMAL_NUMBER),
            default => throw new \LogicException(\sprintf(
                'The operator : %s used for this operation is not listed in the configured operator for this measurement unit.',
                $operator,
            )),
        };
    }
}
