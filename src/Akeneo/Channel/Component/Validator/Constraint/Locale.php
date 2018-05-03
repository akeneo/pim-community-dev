<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if a locale exists.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Locale extends Constraint
{
    /** @var string */
    public $message = 'The locale "%locale%" does not exist.';

    /** @var string */
    public $propertyPath = null;
}
