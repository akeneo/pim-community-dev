<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups as BaseAddToGroups;

/**
 * Adds many products to many groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddToGroups extends BaseAddToGroups
{
    /**
     * Override to bypass the creation of a proposition
     *
     * @return array
     */
    public function getSavingOptions()
    {
        $options = parent::getSavingOptions();
        $options['bypass_proposition'] = true;

        return $options;
    }
}
