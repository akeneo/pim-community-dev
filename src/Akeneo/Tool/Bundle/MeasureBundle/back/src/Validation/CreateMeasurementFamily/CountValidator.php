<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CountValidator extends ConstraintValidator
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;
    private int $max;

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
    public function validate($createMeasurementFamilyCommand, Constraint $constraint): void
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
                ->setParameter('%limit%', (string)$this->max)
                ->setInvalidValue($createMeasurementFamilyCommand)
                ->setPlural((int)$this->max)
                ->addViolation();
        }
    }
}
