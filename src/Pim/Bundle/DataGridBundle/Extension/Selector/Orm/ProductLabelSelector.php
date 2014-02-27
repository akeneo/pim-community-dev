<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Product label selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductLabelSelector implements SelectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();

        $datasource->getQueryBuilder()
            ->leftJoin($rootAlias.'.family', 'plFamily')
            ->leftJoin(
                $rootAlias.'.values',
                'plValues',
                'WITH',
                'plValues.attribute = plFamily.attributeAsLabel '
                .'AND (plValues.locale = :dataLocale OR plValues.locale IS NULL) '
                .'AND (plValues.scope = :scopeCode OR plValues.scope IS NULL)'
            )
            ->addSelect('plValues.varchar AS productLabel');
    }
}
