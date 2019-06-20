<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ValidationRuleValidator extends ConstraintValidator
{
    public function validate($validationRule, Constraint $constraint)
    {
        if (!$constraint instanceof ValidationRule) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $validationRule,
            [
                new Assert\NotNull(),
                new Assert\Type('string'),
                new Assert\Choice([
                    'choices'  => AttributeValidationRule::VALIDATION_RULE_TYPES,
                    'multiple' => false,
                ]),
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}
