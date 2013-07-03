<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueAddressTypes extends Constraint
{
    public $message = 'Different addresses cannot have same type.';
}
