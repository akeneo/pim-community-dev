<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WhenUsedInAProductAttributeShouldBeAbleToUpdateOnlyLabelsAndSymbolAndAddUnitsValidator extends ConstraintValidator
{
    /** @var IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily */
    private $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
    ) {
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily = $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    /**
     * @inheritDoc
     */
    public function validate($saveMeasurementFamily, Constraint $constraint)
    {
        $isMeasureFamilyLockedForUpdates = $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
            ->execute($saveMeasurementFamily->code);

        if (!$isMeasureFamilyLockedForUpdates) {
            return;
        }

        try {
            $measurementFamily = $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($saveMeasurementFamily->code));
        } catch (MeasurementFamilyNotFoundException $exception) {
            return;
        }

        $removedUnits = $this->isTryingToRemoveAUnit($measurementFamily, $saveMeasurementFamily);
        if (0 !== \count($removedUnits)) {
            $this->context->buildViolation(
                WhenUsedInAProductAttributeShouldBeAbleToUpdateOnlyLabelsAndSymbolAndAddUnits::MEASUREMENT_FAMILY_UNIT_REMOVAL_NOT_ALLOWED,
                [
                    '%unit_code%'               => implode(',', $removedUnits),
                    '%measurement_family_code%' => $saveMeasurementFamily->code
                ]
            )
                ->atPath('units')
                ->addViolation();
        }

        $unitsBeingUpdated = $this->isTryingToUpdateTheConvertionOperations(
            $measurementFamily,
            $saveMeasurementFamily
        );
        if ($unitsBeingUpdated) {
            $this->context->buildViolation(
                WhenUsedInAProductAttributeShouldBeAbleToUpdateOnlyLabelsAndSymbolAndAddUnits::MEASUREMENT_FAMILY_OPERATION_UPDATE_NOT_ALLOWED,
                [
                    '%unit_code%'               => implode(',', $unitsBeingUpdated),
                    '%measurement_family_code%' => $saveMeasurementFamily->code
                ]
            )
                ->addViolation();
        }
    }

    private function isTryingToRemoveAUnit(MeasurementFamily $measurementFamily, SaveMeasurementFamilyCommand $saveMeasurementFamily): array
    {
        $normalizedMeasurementFamily = $measurementFamily->normalize();
        $actualUnitCodes = array_map(
            function (array $unit) {
                return $unit['code'];
            },
            $normalizedMeasurementFamily['units']
        );
        $unitCodesToUpdate = array_map(function (array $unit) {
            return $unit['code'];
        }, $saveMeasurementFamily->units);

        return array_diff($actualUnitCodes, $unitCodesToUpdate);
    }

    private function isTryingToUpdateTheConvertionOperations(
        MeasurementFamily $measurementFamily,
        SaveMeasurementFamilyCommand $saveMeasurementFamily
    ): array {
        $serializedUpdatedOperationsPerUnit = $this->serializeUpdatedOperationsPerUnit($saveMeasurementFamily);
        $serializedActualOperationsPerUnit = $this->serializeActualOperationsPerUnit($measurementFamily);

        $unitsBeingUpdated = [];
        foreach ($serializedActualOperationsPerUnit as $unitCode => $serializedActualUnitOperations) {
            if (isset($serializedUpdatedOperationsPerUnit[$unitCode])
                && $serializedUpdatedOperationsPerUnit[$unitCode] !== $serializedActualUnitOperations) {
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
                function (array $unit) {
                    return json_encode($unit);
                },
                $unit['convert_from_standard']
            );
            $operationsPerUnit[$unitCode] = $serializedOperations;
        }

        return $operationsPerUnit;
    }

    private function serializeActualOperationsPerUnit(MeasurementFamily $measurementFamily): array
    {
        $operationsPerUnit = [];
        $normalizedUnits = $measurementFamily->normalize()['units'];
        foreach ($normalizedUnits as $unit) {
            $unitCode = $unit['code'];
            $serializedOperations = array_map(
                function (array $unit) {
                    return json_encode($unit);
                }, $unit['convert_from_standard']);
            $operationsPerUnit[$unitCode] = $serializedOperations;
        }

        return $operationsPerUnit;
    }
}
