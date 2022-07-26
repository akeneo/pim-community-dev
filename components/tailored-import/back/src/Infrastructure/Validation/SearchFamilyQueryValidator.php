<?php

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SearchFamilyQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SearchFamilyQuery) {
            throw new UnexpectedTypeException($constraint, SearchFamilyQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($value->request->all(), new Collection([
            'fields' => [
                'search' => [
                    new Type('string'),
                ],
                'locale' => [
                    new Type('string'),
                    new NotBlank(),
                ],
            ],
        ], allowExtraFields: true));
    }
}
