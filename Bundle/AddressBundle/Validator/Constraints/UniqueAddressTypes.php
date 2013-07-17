<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueAddressTypes extends Constraint
{
    public $message = 'Several addresses have the same type {{ types }}.';
}
