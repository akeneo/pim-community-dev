<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
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
