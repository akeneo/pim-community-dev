<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for valid number range
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidNumberRange extends Constraint
{
    public const PHP_INT_MAX_REACHED = 'pim_catalog.constraint.php_int_max_reached';
    public $message = 'The max number must be greater than the min number';
    public $invalidNumberMessage = 'This number is not valid';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
