<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for variant group values constraint (forbid axis and identifier)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupValues extends Constraint
{
    /** @var string */
    public $message = 'Variant group "%variant group%" cannot contains axis or identifier as values (%attributes%)';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_variant_group_values_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
