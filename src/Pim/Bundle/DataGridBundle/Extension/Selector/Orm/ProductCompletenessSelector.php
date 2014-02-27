<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Product completeness selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCompletenessSelector implements SelectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();

        $datasource->getQueryBuilder()
            ->leftJoin(
                'PimCatalogBundle:Locale',
                'scLocale',
                'WITH',
                'scLocale.code = :dataLocale'
            )
            ->leftJoin(
                'PimCatalogBundle:Channel',
                'scChannel',
                'WITH',
                'scChannel.code = :scopeCode'
            )
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Completeness',
                'selectCompleteness',
                'WITH',
                'selectCompleteness.locale = scLocale.id AND selectCompleteness.channel = scChannel.id '.
                'AND selectCompleteness.product = '.$rootAlias.'.id'
            )
            ->addSelect('selectCompleteness.ratio AS ratio');
    }
}
