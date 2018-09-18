<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Adapter;

use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter as BaseAdapter;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;

/**
 * Transform Oro filters into Akeneo PIM filters
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class OroToPimGridFilterAdapter extends BaseAdapter
{
    /**
     * @param MassActionDispatcher $massActionDispatcher
     */
    public function __construct(MassActionDispatcher $massActionDispatcher)
    {
        parent::__construct($massActionDispatcher);
    }

    /**
     * {@inheritdoc}
     */
    public function adapt(array $parameters)
    {
        if (in_array($parameters['gridName'], [self::PRODUCT_GRID_NAME])) {
            $filters = $this->massActionDispatcher->getRawFilters($parameters);
        } else {
            $filters = $this->adaptDefaultGrid($parameters);
        }

        return $filters;
    }
}
