<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\Filter;

use Pim\Bundle\DataGridBundle\Extension\Filter\FilterExtension as BaseFilterExtension;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends BaseFilterExtension
{
    /**
     * {@inheritdoc}
     *
     * We override this method to add a new grid that can use the category filter
     */
    protected function getCategoryFilterConfig($gridname)
    {
        $gridConfigs = [
            'asset-grid' => [
                'type'      => 'asset_category',
                'data_name' => 'category'
            ]
        ];

        return isset($gridConfigs[$gridname]) ? $gridConfigs[$gridname] : parent::getCategoryFilterConfig($gridname);
    }
}
