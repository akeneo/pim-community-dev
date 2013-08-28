<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for valid default value of attribute
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDefaultValue extends Constraint
{
    /**
     * Violation messages
     */
    public $dateFormatMessage = 'This date format is not valid.';
    public $dateMessage       = 'This value should be between the min and max date.';
    public $negativeMessage   = 'This value should be greater than or equal to 0';
    public $numberMessage     = 'This value should be between the min and max number.';
    public $decimalsMessage   = 'This value should be a whole number.';
    public $charactersMessage = 'This value should not exceed max characters.';
    public $regexpMessage     = 'This value should match the regular expression.';

    /**
     * Property path
     *
     * @var string
     */
    public $propertyPath = 'defaultValue';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
