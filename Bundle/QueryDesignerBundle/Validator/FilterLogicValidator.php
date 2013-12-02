<?php

namespace Oro\Bundle\QueryDesignerBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FilterLogicValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (preg_replace('/\d+|AND|OR|\(|\)|\s/i', '', $value) !== '') {
            $this->context->addViolation('Extra tokens in filter');
        }

        $openParenthesesCount = substr_count($value, '(');
        $closeParenthesesCount = substr_count($value, ')');
        if (($openParenthesesCount > 0 || $closeParenthesesCount > 0) &&  $openParenthesesCount !== $closeParenthesesCount) {
            if ($openParenthesesCount > $closeParenthesesCount) {
                $this->context->addViolation('Extra open parenthesis');
            } else {
                $this->context->addViolation('Extra close parenthesis');
            }
        }
    }
}
