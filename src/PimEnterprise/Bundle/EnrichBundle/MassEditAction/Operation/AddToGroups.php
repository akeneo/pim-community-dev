<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups as BaseAddToGroups;

/**
 * Adds many products to many groups
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddToGroups extends BaseAddToGroups
{
    /**
     * {@inheritdoc}
     *
     * We override the parent job to apply rules in further step
     */
    public function getBatchJobCode()
    {
        return 'add_product_value_with_permission_and_rules';
    }
}
