<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

/**
 * Allows to apply a Franklin subscription filter on the Query builder.
 */
class FranklinSubscriptionFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $filterDatasource, $data)
    {
        $filterValue = isset($data['value']) ? (bool) $data['value'] : null;

        if (null === $filterValue) {
            return false;
        }

        $this->util->applyFilter($filterDatasource, 'franklin_subscription', '=', $filterValue);

        return true;
    }
}
