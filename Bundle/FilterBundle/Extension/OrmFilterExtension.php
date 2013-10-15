<?php

namespace Oro\Bundle\FilterBundle\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\FilterBundle\Extension\Orm\FilterInterface;

class OrmFilterExtension extends AbstractExtension
{
    /**
     * Configuration tree paths
     */
    const FILTERS_PATH         = '[filters]';
    const COLUMNS_PATH         = '[filters][columns]';
    const DEFAULT_FILTERS_PATH = '[filters][default]';

    /**
     * Query param
     */
    const FILTER_ROOT_PARAM = '_filter';

    /** @var FilterInterface[] */
    protected $filters;

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $filters = $this->accessor->getValue($config, self::COLUMNS_PATH) ? : array();

        if (!$filters) {
            return false;
        }

        // validate extension configuration
        $this->validateConfiguration(
            new Configuration(array_keys($this->filters)),
            array('filters' => $this->accessor->getValue($config, self::FILTERS_PATH))
        );

        return $this->accessor->getValue($config, Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $toApply = $this->getFiltersToApply($config);

        foreach ($toApply as $column => $filter) {
            list($definition, $value) = $filter;

            $filter = $this->getFilterObject($definition);

        }
    }

    /**
     * Add filter to array of available filters
     *
     * @param string          $name
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function addFilter($name, FilterInterface $filter)
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Prepare filters array
     *
     * @param array $config
     *
     * @return array
     */
    protected function getFiltersToApply(array $config)
    {
        $result = array();

        $filters = $this->accessor->getValue($config, self::COLUMNS_PATH);

        $defaultFilters = $this->accessor->getValue($config, self::DEFAULT_FILTERS_PATH) ? : array();
        $filterBy       = $this->requestParams->get(self::FILTER_ROOT_PARAM) ? : $defaultFilters;

        foreach ($filterBy as $column => $value) {
            if ($filter = $this->accessor->getValue($filters, "[$column]")) {
                $result[$column] = array($filter, $value);
            }
        }

        return $result;
    }

    /**
     * Returns prepared filter object
     *
     * @param array  $config
     *
     * @return FilterInterface
     */
    protected function getFilterObject(array $config)
    {
        $type = $this->accessor->getValue($config, '[type]');

        $property = $this->filters[$type];
        $property->init($config);

        return $property;
    }
}
