<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

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
