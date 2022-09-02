<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * This validator checks that several conditions are respected when saving a Measurement Family which is linked to a
 * Table Column:
 *  - No unit has been removed from the family
 *  - No operation has been edited for any unit of the family
 */
final class MeasurementFamilyUsedInATableAttributeValidator extends ConstraintValidator
{
    public function __construct(
        private MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        private IsMeasurementFamilyLinkedToATableColumn $isMeasurementFamilyLinkedToATableColumn
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($saveMeasurementFamily, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementFamilyUsedInATableAttribute::class);
        Assert::isInstanceOf($saveMeasurementFamily, SaveMeasurementFamilyCommand::class);

        try {
            $measurementFamily = $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($saveMeasurementFamily->code));
        } catch (MeasurementFamilyNotFoundException) {
            return;
        }

        if (!$this->isMeasurementFamilyLinkedToATableColumn->forCode($saveMeasurementFamily->code)) {
            return;
        }

        if (
            $this->isTryingToRemoveAUnit($measurementFamily, $saveMeasurementFamily)
            || $this->isTryingToUpdateTheConversionOperations($measurementFamily, $saveMeasurementFamily)
        ) {
            $this->context->buildViolation($constraint->message)->atPath('units')->addViolation();
        }
    }

    private function isTryingToRemoveAUnit(MeasurementFamily $measurementFamily, SaveMeasurementFamilyCommand $saveMeasurementFamily): bool
    {
        $normalizedMeasurementFamily = $measurementFamily->normalize();
        $currentUnitCodes = array_map(
            static fn (array $unit) => $unit['code'],
            $normalizedMeasurementFamily['units']
        );
        $unitCodesToUpdate = array_map(static fn (array $unit) => $unit['code'], $saveMeasurementFamily->units);

        return [] !== array_diff($currentUnitCodes, $unitCodesToUpdate);
    }

    private function isTryingToUpdateTheConversionOperations(
        MeasurementFamily $measurementFamily,
        SaveMeasurementFamilyCommand $saveMeasurementFamily
    ): array {
        $serializedUpdatedOperationsPerUnit = $this->serializeUpdatedOperationsPerUnit($saveMeasurementFamily);
        $serializedCurrentOperationsPerUnit = $this->serializeCurrentOperationsPerUnit($measurementFamily);

        $unitsBeingUpdated = [];
        foreach ($serializedCurrentOperationsPerUnit as $unitCode => $serializedCurrentUnitOperations) {
            if (isset($serializedUpdatedOperationsPerUnit[$unitCode])
                && $serializedUpdatedOperationsPerUnit[$unitCode] !== $serializedCurrentUnitOperations) {
                $unitsBeingUpdated[] = $unitCode;
            }
        }

        return $unitsBeingUpdated;
    }

    private function serializeUpdatedOperationsPerUnit(SaveMeasurementFamilyCommand $saveMeasurementFamily): array
    {
        $operationsPerUnit = [];
        foreach ($saveMeasurementFamily->units as $unit) {
            $unitCode = $unit['code'];
            $serializedOperations = array_map(
                static fn (array $unit) => json_encode($unit),
                $unit['convert_from_standard']
            );
            $operationsPerUnit[$unitCode] = $serializedOperations;
        }

        return $operationsPerUnit;
    }

    private function serializeCurrentOperationsPerUnit(MeasurementFamily $measurementFamily): array
    {
        $operationsPerUnit = [];
        $normalizedUnits = $measurementFamily->normalize()['units'];
        foreach ($normalizedUnits as $unit) {
            $unitCode = $unit['code'];
            $serializedOperations = array_map(
                static fn (array $unit) => json_encode($unit),
                $unit['convert_from_standard']
            );
            $operationsPerUnit[$unitCode] = $serializedOperations;
        }

        return $operationsPerUnit;
    }
}
