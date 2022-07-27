<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Type;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AutomationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Automation) {
            throw new UnexpectedTypeException($constraint, Automation::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'running_user_groups' => new All([new NotEqualTo('All'), new Type('string')]),
            ],
        ]));
    }
}
