<?php

namespace AkeneoMeasureBundle\Validation\DeleteMeasurementFamily;

use AkeneoMeasureBundle\Application\DeleteMeasurementFamily\DeleteMeasurementFamilyCommand;
use AkeneoMeasureBundle\Infrastructure\Structure\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ShouldNotBeUsedByProductAttributeValidator extends ConstraintValidator
{
    /** @var IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface */
    private $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;

    public function __construct(
        IsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyInterface $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
    ) {
        $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily = $isThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
    }

    /**
     * @inheritDoc
     */
    public function validate($deleteMeasurementFamily, Constraint $constraint)
    {
        if (!$constraint instanceof ShouldNotBeUsedByProductAttribute) {
            throw new UnexpectedTypeException($constraint, ShouldNotBeUsedByProductAttribute::class);
        }

        if (!$deleteMeasurementFamily instanceof DeleteMeasurementFamilyCommand) {
            throw new UnexpectedTypeException($deleteMeasurementFamily, DeleteMeasurementFamilyCommand::class);
        }

        $isMeasureFamilyLockedForUpdates = $this->isThereAtLeastOneAttributeConfiguredWithMeasurementFamily
            ->execute($deleteMeasurementFamily->code);

        if ($isMeasureFamilyLockedForUpdates) {
            $this->context
                ->buildViolation(ShouldNotBeUsedByProductAttribute::MEASUREMENT_FAMILY_REMOVAL_NOT_ALLOWED)
                ->addViolation();
        }
    }
}
