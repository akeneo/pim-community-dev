<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterData extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'filter_data_validator';
    }
}