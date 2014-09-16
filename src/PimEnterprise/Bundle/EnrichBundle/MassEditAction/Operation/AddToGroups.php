<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups as BaseAddToGroups;

/**
 * Adds many products to many groups
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AddToGroups extends BaseAddToGroups
{
    /**
     * Override to bypass the creation of a product draft
     *
     * @return array
     */
    public function getSavingOptions()
    {
        $options = parent::getSavingOptions();
        $options['bypass_product_draft'] = true;

        return $options;
    }
}
