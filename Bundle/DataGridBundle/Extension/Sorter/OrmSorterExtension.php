<?php

namespace Oro\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
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
    public function isApplicable(array $config)
    {
        $isApplicable = $this->accessor->getValue($config, Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE
            && is_array($this->accessor->getValue($config, self::COLUMNS_PATH));

        $this->validateConfiguration(
            new Configuration(),
            ['sorters' => $this->accessor->getValue($config, self::SORTERS_PATH)]
        );

        return $isApplicable;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $sorters   = $this->getSortersToApply($config);
        $multisort = $this->accessor->getValue($config, self::MULTISORT_PATH) ? : false;
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
    public function visitMetadata(array $config, \stdClass $data)
    {
        $multisort = $this->accessor->getValue($config, self::MULTISORT_PATH) ? : false;

        $data->state            = isset($data->state) && is_array($data->state) ? $data->state : [];
        $data->state['sorters'] = isset($data->state['sorters']) && is_array($data->state['sorters'])
            ? $data->state['sorters'] : [];

        $sorters = $this->getSorters($config);
        foreach (array_keys($sorters) as $name) {
            if (isset($data->columns->{$name})) {
                $data->columns->{$name}['sortable'] = true;
            }
        }

        $data->{DatagridInterface::METADATA_OPTIONS_KEY}['multipleSorting'] = $multisort;

        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $column => $definition) {
            list($direction) = $definition;
            $data->state['sorters'][$column] = $this->normalizeDirection($direction);

            if (!$multisort) {
                break;
            }
        }
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
     * Try to guess data_name from column definition
     *
     * @param array $config
     *
     * @return array
     */
    protected function getSorters(array $config)
    {
        $sorters = $this->accessor->getValue($config, self::COLUMNS_PATH);
        $columns = $this->accessor->getValue($config, FormatterConfiguration::COLUMNS_PATH) ? : [];

        $sorters = array_intersect_key($sorters, $columns);
        foreach ($sorters as $name => $definition) {
            $definition = is_array($definition) ? $definition : [];
            if (!$this->accessor->getValue($definition, sprintf('[%s]', PropertyInterface::DATA_NAME_KEY))) {
                $definition['data_name'] = $this->accessor->getValue(
                    $columns[$name],
                    sprintf('[%s]', PropertyInterface::DATA_NAME_KEY)
                ) ? : $name;
            }

            $sorters[$name] = $definition;
        }

        return $sorters;
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
        $result = [];

        $sorters = $this->getSorters($config);

        $defaultSorters = $this->accessor->getValue($config, self::DEFAULT_SORTERS_PATH) ? : [];
        $sortBy         = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ? : $defaultSorters;

        foreach ($sortBy as $column => $direction) {
            if ($sorter = $this->accessor->getValue($sorters, sprintf('[%s]', $column))) {
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
