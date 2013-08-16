<?php

namespace Oro\Bundle\EmailBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class VariablesConstraint extends Constraint
{
    public $message = 'oro_email.validators.emailtemplate.contains.not_allowed_calls';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_email.variables_validator';
    }
}
