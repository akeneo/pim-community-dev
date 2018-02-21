<?php

namespace Pim\Component\Connector\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for product model export filter data.
 * Filter data are Product Model Query Builder filters.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelFilterData extends Constraint
{
    /** @var string */
    public $message = 'invalid_filter_data';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'product_model_filter_data_validator';
    }
}
