<?php

namespace Oro\Bundle\FilterBundle\Extension;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
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
        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            if ($value = $this->accessor->getValue($values, '[' . $filter->getName() . ']')) {
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
        $data->filters          = array();
        $data->filters['state'] = array();

        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            if ($value = $this->accessor->getValue($values, '[' . $filter->getName() . ']')) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $data->filters['state'][$filter->getName()] = $value;
                }
            }
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
     * @return FilterInterface[]
     */
    protected function getFiltersToApply(array $config)
    {
        $filters       = array();
        $filtersConfig = $this->accessor->getValue($config, self::COLUMNS_PATH);

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
        $result = array();

        $filters = $this->accessor->getValue($config, self::COLUMNS_PATH);

        $defaultFilters = $this->accessor->getValue($config, self::DEFAULT_FILTERS_PATH) ? : array();
        $filterBy       = $this->requestParams->get(self::FILTER_ROOT_PARAM) ? : $defaultFilters;

        foreach ($filterBy as $column => $value) {
            if ($this->accessor->getValue($filters, "[$column]")) {
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
        $type = $this->accessor->getValue($config, '[type]');

        $property = $this->filters[$type];
        $property->init($name, $config);

        return $property;
    }
}
