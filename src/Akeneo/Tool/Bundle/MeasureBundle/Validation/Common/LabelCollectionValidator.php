<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\Common;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LabelCollectionValidator extends ConstraintValidator
{
    /**
     * @param mixed $labels The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($labels, Constraint $constraint)
    {
        if (empty($labels)) {
            return;
        }

        $validator = Validation::createValidator();

        foreach ($labels as $localeCode => $label) {
            $this->validateLocaleCode($validator, $localeCode);
            $this->validateLabelForLocale($validator, $label, $localeCode);
        }
    }

    /**
     * @param mixed $localeCode
     */
    private function validateLocaleCode(ValidatorInterface $validator, $localeCode): void
    {
        $violations = $validator->validate($localeCode, [
            new Constraints\NotBlank(),
            new Constraints\Type(['type' => 'string']),
            new Constraints\Length(['max' => 100])
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                ->atPath(sprintf('[%s]', $localeCode))
                ->addViolation();
            }
        }
    }

    private function validateLabelForLocale(ValidatorInterface $validator, $label, $localeCode): void
    {
        $violations = $validator->validate($label, [
            new Constraints\NotNull(),
            new Constraints\Type(['type' => 'string']),
            new Constraints\Length(['max' => 100]),
        ]);

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                )
                ->atPath(sprintf('[%s]', $localeCode))
                ->addViolation();
            }
        }
    }
}
