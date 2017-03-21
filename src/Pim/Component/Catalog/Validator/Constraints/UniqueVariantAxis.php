<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for unique variant group axis values
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueVariantAxis extends Constraint
{
    /**
     * Violation message for already existing axis combination
     *
     * @var string
     */
    public $message = 'Group "%variant group%" already contains another product with values "%values%"';

    /**
     * Violation message for missing axis value
     *
     * @var string
     */
    public $missingAxisMessage = 'Product "%product%" should have value for axis "%axis%" of variant group "%group%"';

    /** @var string */
    public $propertyPath = 'variant_group';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_unique_variant_axis_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
