<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that all specified properties are not set or set to null.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NullProperties extends Constraint
{
    /** @var string */
    public $message = 'This value should be blank.';

    /** @var string */
    public $propertyPath = 'properties';

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
