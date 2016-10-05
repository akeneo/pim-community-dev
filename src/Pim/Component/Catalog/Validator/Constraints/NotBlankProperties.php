<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if properties are not left blank
 *
 * @author    Fabien Lemoine <fabien.lemoine@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotBlankProperties extends Constraint
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
}
