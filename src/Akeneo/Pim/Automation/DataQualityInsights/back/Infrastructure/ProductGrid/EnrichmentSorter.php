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
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;

final class EnrichmentSorter implements SorterInterface
{
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        if (!$datasource instanceof ProductDatasource
            && !$datasource instanceof ProductAndProductModelDatasource
        ) {
            throw new \InvalidArgumentException('Datasource must be a product datasource.');
        }

        $datasource->getProductQueryBuilder()->addSorter($field, $direction);
    }
}
