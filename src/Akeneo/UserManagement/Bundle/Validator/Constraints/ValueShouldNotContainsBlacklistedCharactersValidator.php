<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for user inputs where some characters are now blacklisted in order to prevent injection of malicious code
 */
class ValueShouldNotContainsBlacklistedCharactersValidator extends ConstraintValidator
{
    private const BLACKLISTED_CHARACTERS = ['<', '>', '&', '"'];

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        //strpbrk returns a string if one of the characters in second argument string was found
        if (!empty(strpbrk($value, implode('', self::BLACKLISTED_CHARACTERS)))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ items }}', implode(', ', self::BLACKLISTED_CHARACTERS))
                ->addViolation();
        }
    }
}
