<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegularExpressionValidator extends ConstraintValidator
{
    public function validate($regularExpression, Constraint $constraint)
    {
        if (!$constraint instanceof RegularExpression) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($regularExpression, [new Assert\Type('string')]);
        if ($violations->count() > 0) {
            $this->addViolations($violations);

            return;
        }

        if (null !== $regularExpression && false === @preg_match($regularExpression, '')) {
            $this->context->buildViolation(RegularExpression::INVALID_REGULAR_EXPRESSION)->addViolation();
        }
    }

    private function addViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }
}
