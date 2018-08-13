<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that the attribute used with an option is valid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeForOption extends Constraint
{
    /** @var string */
    public $invalidAttributeMessage = 'Attribute "%attribute%" does not support options. Only attributes of type "%attribute_types%" support options';

    /** @var string */
    public $propertyPath = null;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_attribute_type_for_option_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
