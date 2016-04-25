<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup as BaseAddToVariantGroup;

/**
 * Operation to add products to variant groups
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddToVariantGroup extends BaseAddToVariantGroup
{
    /**
     * {@inheritdoc}
     *
     * We override the parent job to apply rules in further step
     */
    public function getBatchJobCode()
    {
        return 'add_to_variant_group_with_rules';
    }
}
