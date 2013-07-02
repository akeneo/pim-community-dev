<?php

namespace Oro\Bundle\AddressBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueAddressTypes extends Constraint
{
    public $message = 'Different addresses cannot have same type.';
}
