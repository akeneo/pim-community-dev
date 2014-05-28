<?php

namespace PimEnterprise\Bundle\DataGridBundle\Extension\Filter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Filter\AbstractFilterExtension;
use PimEnterprise\Bundle\DataGridBundle\Datasource\ProposalDatasource;

/**
 * Proposal filter extension
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalFilterExtension extends AbstractFilterExtension
{
    /**
     * {@inheritdoc}
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return $datasourceType === ProposalDatasource::TYPE;
    }
}
