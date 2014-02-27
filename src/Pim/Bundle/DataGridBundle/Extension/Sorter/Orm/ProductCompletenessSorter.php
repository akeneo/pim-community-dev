<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Product completeness sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessSorter implements SorterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        return function (DatasourceInterface $datasource, $field, $direction) {

            $rootAlias = $datasource->getQueryBuilder()->getRootAlias();
            $datasource->getQueryBuilder()
                ->leftJoin(
                    'PimCatalogBundle:Locale',
                    'sorterCLocale',
                    'WITH',
                    'sorterCLocale.code = :dataLocale'
                )
                ->leftJoin(
                    'PimCatalogBundle:Channel',
                    'sorterCChannel',
                    'WITH',
                    'sorterCChannel.code = :scopeCode'
                )
                ->leftJoin(
                    'Pim\Bundle\CatalogBundle\Model\Completeness',
                    'sorterCompleteness',
                    'WITH',
                    'sorterCompleteness.locale = sorterCLocale.id AND sorterCompleteness.channel = sorterCChannel.id '.
                    'AND sorterCompleteness.product = '.$rootAlias.'.id'
                )
                ->addOrderBy($field, $direction);
        };
    }
}
