<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

class OrmSorterExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const SORTERS_PATH         = '[sorters]';
    const COLUMNS_PATH         = '[sorters][columns]';
    const MULTISORT_PATH       = '[sorters][multiple_sorting]';
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
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns      = $config->offsetGetByPath(self::COLUMNS_PATH);
        $isApplicable = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) === OrmDatasource::TYPE
            && is_array($columns);

        $this->validateConfiguration(new Configuration(), ['sorters' => $config->offsetGetByPath(self::SORTERS_PATH)]);

        return $isApplicable;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $sorters   = $this->getSortersToApply($config);
        $multisort = $config->offsetGetByPath(self::MULTISORT_PATH, false);
        foreach ($sorters as $definition) {
            list($direction, $sorter) = $definition;

            $sortKey = $sorter['data_name'];
            $datasource->getQuery()->addOrderBy($sortKey, $direction);

            if (!$multisort) {
                break;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $multisort    = $config->offsetGetByPath(self::MULTISORT_PATH, false);
        $sortersState = $data->offsetGetByPath('[state][sorters]', []);

        $sorters = $this->getSorters($config);
        foreach ($data->offsetGetOr('columns', []) as $key => $column) {
            if (isset($column['name']) && isset($sorters[$column['name']])) {
                $data->offsetSetByPath(sprintf('[columns][%s][sortable]', $key), true);
            }
        }
        $data->offsetAddToArray(MetadataObject::OPTIONS_KEY, ['multipleSorting' => $multisort]);

        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $column => $definition) {
            list($direction) = $definition;
            $sortersState[$column] = $this->normalizeDirection($direction);

            if (!$multisort) {
                break;
            }
        }

        $data->offsetAddToArray('state', ['sorters' => $sortersState]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        // should visit after all extensions
        return -250;
    }

    /**
     * Retrieve and prepare list of sorters
     * Try to guess data_name from column definition
     *
     * @param DatagridConfiguration $config
     *
     * @return array
     */
    protected function getSorters(DatagridConfiguration $config)
    {
        $sorters = $config->offsetGetByPath(self::COLUMNS_PATH);
        $columns = $config->offsetGetByPath(FormatterConfiguration::COLUMNS_PATH, []);

        $sorters = array_intersect_key($sorters, $columns);
        foreach ($sorters as $name => $definition) {
            $definition = is_array($definition) ? $definition : [];

            if (!isset($definition[PropertyInterface::DATA_NAME_KEY])) {
                $definition[PropertyInterface::DATA_NAME_KEY] = isset($columns[$name][PropertyInterface::DATA_NAME_KEY])
                    ? $columns[$name][PropertyInterface::DATA_NAME_KEY] : $name;
            }

            $sorters[$name] = $definition;
        }

        return $sorters;
    }

    /**
     * Prepare sorters array
     *
     * @param DatagridConfiguration $config
     *
     * @return array
     */
    protected function getSortersToApply(DatagridConfiguration $config)
    {
        $result = [];

        $sorters = $this->getSorters($config);

        $defaultSorters = $config->offsetGetByPath(self::DEFAULT_SORTERS_PATH, []);
        $sortBy         = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ? : $defaultSorters;

        foreach ($sortBy as $column => $direction) {
            $sorter = isset($sorters[$column]) ? $sorters[$column] : false;

            if ($sorter !== false) {
                $direction       = $this->normalizeDirection($direction);
                $result[$column] = [$direction, $sorter];
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
            case in_array($direction, [self::DIRECTION_ASC, self::DIRECTION_DESC], true):
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
