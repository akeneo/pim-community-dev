<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Validation\SaveMeasurementFamily;

use AkeneoMeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use AkeneoMeasureBundle\Model\MeasurementFamilyCode;
use AkeneoMeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CountValidator extends ConstraintValidator
{
    /** @var MeasurementFamilyRepositoryInterface */
    private $measurementFamilyRepository;

    /** @var int */
    private $max;

    public function __construct(
        MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        int $max
    ) {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($saveMeasurementFamilyCommand, Constraint $constraint)
    {
        if (!$constraint instanceof Count) {
            throw new UnexpectedTypeException($constraint, Count::class);
        }

        if (!$saveMeasurementFamilyCommand instanceof SaveMeasurementFamilyCommand) {
            throw new UnexpectedTypeException($saveMeasurementFamilyCommand, SaveMeasurementFamilyCommand::class);
        }

        $excludedMeasurementFamilyCode = MeasurementFamilyCode::fromString($saveMeasurementFamilyCommand->code);

        $count = $this->measurementFamilyRepository->countAllOthers($excludedMeasurementFamilyCode);

        if ($count >= $this->max) {
            $this->context->buildViolation(Count::MAX_MESSAGE)
                ->setParameter('%limit%', $this->max)
                ->setInvalidValue($saveMeasurementFamilyCommand)
                ->setPlural((int)$this->max)
                ->addViolation();
        }
    }
}
