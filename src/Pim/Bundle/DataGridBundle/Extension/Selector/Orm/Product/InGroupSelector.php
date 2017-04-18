<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * In group selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupSelector implements SelectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        // TODO: to fix with TIP-664
        return;
        $currentGroupId = $configuration->offsetGetByPath('[source][current_group_id]');

        $esQb = $datasource->getQueryBuilder();
        $qb = $esQb->getStorageQb();
        $rootAlias = $qb->getRootAlias();

        $inGroupExpr = sprintf('CASE WHEN :currentGroup MEMBER OF p.groups THEN true ELSE false END', $rootAlias);
        $qb->addSelect($inGroupExpr.' AS in_group');
        $qb->setParameter('currentGroup', intval($currentGroupId));
    }
}
