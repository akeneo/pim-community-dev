<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsPrimary extends Constraint
{
    public $message = 'One of addresses must be set as primary.';
}
