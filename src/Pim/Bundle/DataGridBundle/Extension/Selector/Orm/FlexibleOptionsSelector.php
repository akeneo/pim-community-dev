<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector\Orm;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

/**
 * Flexible options selector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleOptionsSelector implements SelectorInterface
{
    /**
     * @param SelectorInterface $predecessor
     */
    public function __construct(SelectorInterface $predecessor)
    {
        $this->predecessor = $predecessor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, DatagridConfiguration $configuration)
    {
        $this->predecessor->apply($datasource, $configuration);

        $datasource->getQueryBuilder()
            ->leftJoin(
                'values.options',
                'multioptions'
            )
            ->addSelect('multioptions')

            ->leftJoin(
                'multioptions.optionValues',
                'multioptionvalues',
                'WITH',
                'multioptionvalues.locale = :dataLocale OR multioptionvalues.locale IS NULL'
            )
            ->addSelect('multioptionvalues');
    }
}

