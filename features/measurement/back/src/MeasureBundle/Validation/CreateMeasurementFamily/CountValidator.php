<?php

declare(strict_types=1);

namespace AkeneoMeasureBundle\Validation\CreateMeasurementFamily;

use AkeneoMeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
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
    public function validate($createMeasurementFamilyCommand, Constraint $constraint)
    {
        if (!$constraint instanceof Count) {
            throw new UnexpectedTypeException($constraint, Count::class);
        }

        if (!$createMeasurementFamilyCommand instanceof CreateMeasurementFamilyCommand) {
            throw new UnexpectedTypeException($createMeasurementFamilyCommand, CreateMeasurementFamilyCommand::class);
        }

        $excludedMeasurementFamilyCode = MeasurementFamilyCode::fromString($createMeasurementFamilyCommand->code);

        $count = $this->measurementFamilyRepository->countAllOthers($excludedMeasurementFamilyCode);

        if ($count >= $this->max) {
            $this->context->buildViolation(Count::MAX_MESSAGE)
                ->setParameter('%limit%', $this->max)
                ->setInvalidValue($createMeasurementFamilyCommand)
                ->setPlural((int)$this->max)
                ->addViolation();
        }
    }
}
