<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that a product has all the axes of its variant group
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasVariantAxes extends Constraint
{
    /** @var string */
    public $message =
        'The product "%product%" is in the variant group "%variant%" but it misses the following axes: %axes%.'
    ;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_has_variant_axes_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
