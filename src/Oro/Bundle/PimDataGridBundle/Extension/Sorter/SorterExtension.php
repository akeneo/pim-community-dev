<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

/**
 * Sorter extension, storage agnostic
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SorterExtension extends AbstractExtension
{
    /** @staticvar string Query param */
    const SORTERS_ROOT_PARAM = '_sort_by';

    /** @staticvar string Ascending sorting direction */
    const DIRECTION_ASC = "ASC";

    /** @staticvar string Descending sorting direction */
    const DIRECTION_DESC = "DESC";

    /**
     * @var SorterInterface[]
     */
    protected $sorters;

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        return is_array($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $this->validateConfiguration(
            new Configuration(),
            ['sorters' => $config->offsetGetByPath(Configuration::SORTERS_PATH)]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $definition) {
            list($direction, $sorter) = $definition;
            $sortKey = $sorter['data_name'];
            if (isset($sorter['sorter']) && $sorter['sorter'] !== null) {
                $sorterAlias = $sorter['sorter'];
            } else {
                $sorterAlias = 'field';
            }
            if (!isset($this->sorters[$sorterAlias])) {
                throw new \LogicException(
                    sprintf(
                        'The sorter "%s" used to configure the column "%s" does not exist',
                        $sorterAlias,
                        $sortKey
                    )
                );
            }
            $sorter = $this->sorters[$sorterAlias];
            $sorter->apply($datasource, $sortKey, $direction);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        $multisort = $config->offsetGetByPath(Configuration::MULTISORT_PATH, false);
        $sorters = $this->getSorters($config);

        $proceed = [];
        foreach ($data->offsetGetOr('columns', []) as $key => $column) {
            if (isset($column['name']) && isset($sorters[$column['name']])) {
                $data->offsetSetByPath(sprintf('[columns][%s][sortable]', $key), true);
                $proceed[] = $column['name'];
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
     * {@inheritdoc}
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
        $sortBy = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ?: $defaultSorters;

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
            case ($direction == false):
                $direction = self::DIRECTION_DESC;
                break;
            default:
                $direction = self::DIRECTION_ASC;
        }

        return $direction;
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
