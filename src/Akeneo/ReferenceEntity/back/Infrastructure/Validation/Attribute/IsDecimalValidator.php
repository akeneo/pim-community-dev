<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class IsDecimalValidator extends ConstraintValidator
{
    public function validate($isDecimal, Constraint $constraint)
    {
        if (!$constraint instanceof IsDecimal) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($isDecimal, [
            new Assert\NotNull(),
            new Assert\Type('boolean'),
        ]);

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
