<?php

namespace Akeneo\Channel\Component\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConversionUnits extends Constraint
{
    /** @var string */
    public $invalidAttributeCode = 'The attribute "%attributeCode%" does not exist.';

    /** @var string */
    public $notAMetricAttribute = 'The attribute "%attributeCode%" is not a metric attribute.';

    /** @var string */
    public $invalidUnitCode = 'The unit "%unitCode%" does not exist or does not belong to the default metric family of the given attribute "%attributeCode%".';
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_conversion_units_validator';
    }
}
