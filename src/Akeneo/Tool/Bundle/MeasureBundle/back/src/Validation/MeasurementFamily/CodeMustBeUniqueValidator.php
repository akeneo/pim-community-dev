<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
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

        if ($this->isCodeAlreadyUsed($value)) {
            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->addViolation();
        }
    }

    private function isCodeAlreadyUsed(string $code)
    {
        try {
            $this->measurementFamilyRepository->getByCode(MeasurementFamilyCode::fromString($code));
            return true;
        } catch (MeasurementFamilyNotFoundException $ex) {
            return false;
        }
    }
}
