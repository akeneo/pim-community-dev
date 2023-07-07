<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\Unit;

use Akeneo\Tool\Bundle\MeasureBundle\Application\ValidateUnit\ValidateUnitCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CodeMustBeUniqueValidator extends ConstraintValidator
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CodeMustBeUnique) {
            throw new UnexpectedTypeException($constraint, CodeMustBeUnique::class);
        }

        if (!$value instanceof ValidateUnitCommand) {
            throw new UnexpectedTypeException($value, ValidateUnitCommand::class);
        }

        if ($this->measurementFamilyAlreadyHasUnitWithCode($value->measurementFamilyCode, $value->code)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->setInvalidValue($value->code)
                ->addViolation();
        }
    }

    private function measurementFamilyAlreadyHasUnitWithCode(string $measurementFamilyCode, string $code): bool
    {
        $measurementFamily = $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($measurementFamilyCode));

        foreach ($measurementFamily->normalize()['units'] as $unit) {
            if (strtolower($unit['code']) === strtolower($code)) {
                return true;
            }
        }

        return false;
    }
}
