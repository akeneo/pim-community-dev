<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if properties are not left null.
 *
 * @author    Fabien Lemoine <fabien.lemoine@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotNullProperties extends Constraint
{
    /** @var string */
    public $message = 'This value should not be blank.';

    /** @var array */
    public $properties = [];

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'properties';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['properties'];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_not_null_properties_validator';
    }
}
