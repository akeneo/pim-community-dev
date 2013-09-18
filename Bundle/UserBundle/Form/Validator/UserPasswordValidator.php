<?php

namespace Oro\Bundle\UserBundle\Form\Validator;

use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator as ParentPasswordValidator;
use Symfony\Component\Validator\Constraint;

class UserPasswordValidator extends ParentPasswordValidator
{
    public function validate($password, Constraint $constraint)
    {
        if (empty($password)) {
            return true;
        }

        parent::validate($password, $constraint);
    }
}
