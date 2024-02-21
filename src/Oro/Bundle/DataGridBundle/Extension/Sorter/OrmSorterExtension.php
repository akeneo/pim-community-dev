<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

class OrmSorterExtension extends AbstractExtension
{
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
        $columns = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
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
        $sorters = $this->getSortersToApply($config);
        $multisort = $config->offsetGetByPath(Configuration::MULTISORT_PATH, false);
        foreach ($sorters as $definition) {
            list($direction, $sorter) = $definition;

            $sortKey = $sorter['data_name'];

            // if need customized behavior, just pass closure under "apply_callback" node
            if (isset($sorter['apply_callback']) && is_callable($sorter['apply_callback'])) {
                $sorter['apply_callback']($datasource, $sortKey, $direction);
            } else {
                $datasource->getQueryBuilder()->addOrderBy($sortKey, $direction);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        $multisort = $config->offsetGetByPath(Configuration::MULTISORT_PATH, false);
        $sorters = $this->getSorters($config);

        $proceed = [];
        foreach ($data->offsetGetOr('columns', []) as $key => $column) {
            if (isset($column['name']) && isset($sorters[$column['name']])) {
                $data->offsetSetByPath(sprintf('[columns][%s][sortable]', $key), true);
                $proceed [] = $column['name'];
            }
        }

        $extraSorters = array_diff(array_keys($sorters), $proceed);
        if (count($extraSorters)) {
            throw new \LogicException(
                sprintf('Could not found column(s) "%s" for sorting', implode(', ', $extraSorters))
            );
        }

        $data->offsetAddToArray(MetadataIterableObject::OPTIONS_KEY, ['multipleSorting' => $multisort]);

        $sortersState = $data->offsetGetByPath('[state][sorters]', []);
        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $column => $definition) {
            list($direction) = $definition;
            $sortersState[$column] = $this->normalizeDirection($direction);
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
     *
     * @param DatagridConfiguration $config
     *
     * @return array
     */
    protected function getSorters(DatagridConfiguration $config)
    {
        $sorters = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        foreach ($sorters as $name => $definition) {
            $definition = is_array($definition) ? $definition : [];
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

        $defaultSorters = $config->offsetGetByPath(Configuration::DEFAULT_SORTERS_PATH, []);
        $sortBy = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ? : $defaultSorters;

        // if default sorter was not specified, just take first sortable column
        if (!$sortBy && $sorters) {
            $names = array_keys($sorters);
            $firstSorterName = reset($names);
            $sortBy = [$firstSorterName => self::DIRECTION_ASC];
        }

        foreach ($sortBy as $column => $direction) {
            $sorter = isset($sorters[$column]) ? $sorters[$column] : false;

            if ($sorter !== false) {
                $direction = $this->normalizeDirection($direction);
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
