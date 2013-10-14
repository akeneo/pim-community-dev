<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

class OrmSorterExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const COLUMNS_PATH         = '[sorters][columns]';
    const MULTISORT_PATH       = '[sorters][enable_multisort]';
    const DEFAULT_SORTERS_PATH = '[sorters][default]';

    /**
     * Query param
     */
    const SORTERS_ROOT_PARAM = '_sort_by';

    /**
     * Ascending sorting direction
     */
    const DIRECTION_ASC = "ASC";

    /**
     * Descending sorting direction
     */
    const DIRECTION_DESC = "DESC";

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        return $this->accessor->getValue($config, Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE
        && is_array($this->accessor->getValue($config, self::COLUMNS_PATH));
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $definition) {
            list($direction, $sorter) = $definition;
            $datasource->getQuery()->addSortOrder($sorter, $direction);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $data)
    {
        $data->sorter            = array();
        $data->sorter['state']   = array();
        $data->sorter['options'] = array(
            'multiple_sorting' => $this->accessor->getValue($config, self::MULTISORT_PATH) ? : false,
        );

        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $column => $definition) {
            list($direction) = $definition;
            $data->sorter['state'][$column] = $this->normalizeDirection($direction);
        }
    }

    /**
     * Prepare sorters array
     *
     * @param array $config
     *
     * @return array
     */
    protected function getSortersToApply(array $config)
    {
        $result = array();

        $sorters = $this->accessor->getValue($config, self::COLUMNS_PATH);

        $defaultSorters = $this->accessor->getValue($config, self::DEFAULT_SORTERS_PATH) ? : array();
        $sortBy         = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ? : $defaultSorters;

        foreach ($sortBy as $column => $direction) {
            if ($sorter = $this->accessor->getValue($sorters, "[$column]")) {
                $direction = $this->normalizeDirection($direction);

                $result[$column] = array($direction, $sorter);
            }
        }

        return $result;
    }

    /**
     * Normalize user input
     *
     * @param string $direction
     *
     * @return string
     */
    protected function normalizeDirection($direction)
    {
        switch (true) {
            case in_array($direction, array(self::DIRECTION_ASC, self::DIRECTION_DESC), true):
                break;
            case ($direction === false):
                $direction = self::DIRECTION_DESC;
                break;
            default:
                $direction = self::DIRECTION_ASC;
        }

        return $direction;
    }
}
