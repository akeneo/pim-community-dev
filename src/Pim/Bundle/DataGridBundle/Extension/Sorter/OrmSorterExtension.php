<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension as OroOrmSorterExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

/**
 * Orm filter extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmSorterExtension extends OroOrmSorterExtension
{
    /**
     * @var SorterInterface[]
     */
    protected $sorters;

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns      = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
        $isApplicable = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) === OrmDatasource::TYPE
            && is_array($columns);

        return $isApplicable;
    }

    /**
     * {@inheritDoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $this->validateConfiguration(
            new Configuration(),
            ['sorters' => $config->offsetGetByPath(Configuration::SORTERS_PATH)]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $sorters   = $this->getSortersToApply($config);
        foreach ($sorters as $definition) {
            list($direction, $sorter) = $definition;
            $sortKey = $sorter['data_name'];
            // if need customized behavior, use sorter service under "sorter" node or a closure in "apply_callback"
            if (isset($sorter['sorter']) && $sorter['sorter'] !== null) {
                $sorterAlias = $sorter['sorter'];
                if (!isset($this->sorters[$sorterAlias])) {
                    throw new \LogicException(
                        sprintf(
                            'The sorter "%s" used to configure the column "%s" not exists',
                            $sorterAlias,
                            $sortKey
                        )
                    );
                }
                $sorter = $this->sorters[$sorterAlias];
                $sorter->apply($datasource, $sortKey, $direction);
            } elseif (isset($sorter['apply_callback']) && is_callable($sorter['apply_callback'])) {
                $sorter['apply_callback']($datasource, $sortKey, $direction);
            } else {
                $datasource->getQueryBuilder()->addOrderBy($sortKey, $direction);
            }
        }
    }

    /**
     * Add sorters to array of available sorters
     *
     * @param string          $name
     * @param SorterInterface $sorter
     *
     * @return $this
     */
    public function addSorter($name, SorterInterface $sorter)
    {
        $this->sorters[$name] = $sorter;

        return $this;
    }
}
