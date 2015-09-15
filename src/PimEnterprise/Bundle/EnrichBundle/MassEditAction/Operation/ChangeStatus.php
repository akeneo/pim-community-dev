<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeStatus as BaseChangeStatus;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus extends BaseChangeStatus
{
    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'update_product_value_with_permission';
    }
}
