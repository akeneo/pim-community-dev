<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups as BaseAddToGroups;

/**
 * Adds many products to many groups
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
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
