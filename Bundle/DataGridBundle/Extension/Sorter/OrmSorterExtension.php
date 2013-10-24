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
    const SORTERS_PATH         = '[sorters]';
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

        $data->sorter            = [];
        $data->sorter['state']   = [];
        $data->sorter['options'] = [
            'multiple_sorting' => $multisort
        ];

        $sorters = $this->getSortersToApply($config);
        foreach ($sorters as $column => $definition) {
            list($direction) = $definition;
            $data->sorter['state'][$column] = $this->normalizeDirection($direction);

            if (!$multisort) {
                break;
            }
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
        $result = [];

        $sorters = $this->accessor->getValue($config, self::COLUMNS_PATH);

        $defaultSorters = $this->accessor->getValue($config, self::DEFAULT_SORTERS_PATH) ? : [];
        $sortBy         = $this->requestParams->get(self::SORTERS_ROOT_PARAM) ? : $defaultSorters;

        foreach ($sortBy as $column => $direction) {
            if ($sorter = $this->accessor->getValue($sorters, "[$column]")) {
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
