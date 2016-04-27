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

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup as BaseAddToVariantGroup;

/**
 * Operation to add products to variant groups
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
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
