<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConversionUnits extends Constraint
{
    /** @var string */
    public $invalidAttributeCode = 'Property "conversion_units" expects a valid attributeCode. The attribute code for the conversion unit does not exist, "%attributeCode%" given.';

    /** @var string */
    public $invalidUnitCode = 'Property "conversion_units" expects a valid unitCode. The metric unit code for the conversion unit does not exist, "%unitCode%" given.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_conversion_units_validator';
    }
}
