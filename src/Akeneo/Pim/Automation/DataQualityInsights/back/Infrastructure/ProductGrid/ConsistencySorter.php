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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Webmozart\Assert\Assert;

final class ConsistencySorter implements SorterInterface
{
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        Assert::implementsInterface($datasource, FilterProductDatasourceAdapterInterface::class);
        $datasource->getProductQueryBuilder()->addSorter($field, $direction);
    }
}
