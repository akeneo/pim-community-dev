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
     * Query param
     */
    const FILTER_ROOT_PARAM = '_filter';

    /** @var FilterInterface[] */
    protected $filters = [];

    /**
     * {@inheritDoc}
     */
    public function isApplicable(array $config)
    {
        $filters = $this->accessor->getValue($config, Configuration::COLUMNS_PATH) ? : [];

        if (!$filters) {
            return false;
        }

        // validate extension configuration
        $this->validateConfiguration(
            new Configuration(array_keys($this->filters)),
            ['filters' => $this->accessor->getValue($config, Configuration::FILTERS_PATH)]
        );

        return $this->accessor->getValue($config, Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(array $config, DatasourceInterface $datasource)
    {
        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            if ($value = $this->accessor->getValue($values, sprintf('[%s]', $filter->getName()))) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if (!($form->isValid() && $filter->apply($datasource->getQuery(), $form->getData()))) {
                    throw new \LogicException(sprintf('Filter %s is not valid', $filter->getName()));
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(array $config, \stdClass $data)
    {
        $data->filters = isset($data->filters) && is_array($data->filters) ? $data->filters : [];
        $state         = $list = [];


        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            if ($value = $this->accessor->getValue($values, sprintf('[%s]', $filter->getName()))) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $state[$filter->getName()] = $value;
                }
            }

            $list[$filter->getName()] = $filter->getMetadata();
        }

        $data->filters['state'] = array_merge(
            isset($data->filters['state']) && is_array($data->filters['state']) ? $data->filters['state'] : [],
            $state
        );
        $data->filters['list']  = array_merge(
            isset($data->filters['list']) && is_array($data->filters['list']) ? $data->filters['list'] : [],
            $list
        );
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
     * @return FilterInterface[]
     */
    protected function getFiltersToApply(array $config)
    {
        $filters       = [];
        $filtersConfig = $this->accessor->getValue($config, Configuration::COLUMNS_PATH);

        foreach ($filtersConfig as $column => $filter) {
            $filters[] = $this->getFilterObject($column, $filter);
        }

        return $filters;
    }

    /**
     * Takes param from request and merge with default filters
     *
     * @param array $config
     *
     * @return array
     */
    protected function getValuesToApply(array $config)
    {
        $result = [];

        $filters = $this->accessor->getValue($config, Configuration::COLUMNS_PATH);

        $defaultFilters = $this->accessor->getValue($config, Configuration::DEFAULT_FILTERS_PATH) ? : [];
        $filterBy       = $this->requestParams->get(self::FILTER_ROOT_PARAM) ? : $defaultFilters;

        foreach ($filterBy as $column => $value) {
            if ($this->accessor->getValue($filters, sprintf('[%s]', $column))) {
                $result[$column] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns prepared filter object
     *
     * @param string $name
     * @param array  $config
     *
     * @return FilterInterface
     */
    protected function getFilterObject($name, array $config)
    {
        $type = $this->accessor->getValue($config, sprintf('[%s]', Configuration::TYPE_KEY));

        $filter = $this->filters[$type];
        $filter->init($name, $config);

        return clone $filter;
    }
}
