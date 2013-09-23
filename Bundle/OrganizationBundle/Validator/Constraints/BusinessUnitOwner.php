<?php

namespace Oro\Bundle\OrganizationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class BusinessUnitOwner extends Constraint
{
    public $message = "Business Unit can't set self as Parent.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}