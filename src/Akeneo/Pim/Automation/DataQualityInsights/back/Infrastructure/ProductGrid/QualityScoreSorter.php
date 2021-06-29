<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreSorter implements SorterInterface
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
