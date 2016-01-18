<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check that variant group have axis
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAxis extends Constraint
{
    /** @var string */
    public $expectedAxisMessage = 'Variant group "%variant group%" must be defined with at least one axis';

    /** @var string */
    public $unexpectedAxisMessage = 'Group "%group%", which is not variant, can not be defined with axes';

    /** @var string */
    public $invalidAxisMessage = 'Attribute "%attribute%" cannot be used as axis of variant group "%group%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_variant_group_axis_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
