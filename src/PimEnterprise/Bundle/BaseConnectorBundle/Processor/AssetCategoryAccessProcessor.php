<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\BaseConnectorBundle\Processor;

/**
 * Asset category access import processor
 * Allows to bind data into a category access and validate them
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AssetCategoryAccessProcessor extends CategoryAccessProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedPermissions()
    {
        return ['viewItems', 'editItems'];
    }
}
