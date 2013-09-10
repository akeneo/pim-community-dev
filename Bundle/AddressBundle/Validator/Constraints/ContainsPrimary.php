<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContainsPrimary extends Constraint
{
    public $message = 'One of items must be set as primary.';
}
