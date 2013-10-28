<?php

namespace Oro\Bundle\FilterBundle\Extension;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
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

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(RequestParameters $requestParams, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        parent::__construct($requestParams);
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
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
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
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
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $filtersState    = $data->offsetGetByPath('[state][filters]', []);
        $filtersMetaData = [];

        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            if ($value = $this->accessor->getValue($values, sprintf('[%s]', $filter->getName()))) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $filtersState[$filter->getName()] = $value;
                }
            }

            $metadata          = $filter->getMetadata();
            $filtersMetaData[] = array_merge(
                $metadata,
                ['label' => $this->translator->trans($metadata['label'])]
            );

        }

        $data->offsetAddToArray('state', ['filters' => $filtersState])
            ->offsetAddToArray('filters', $filtersMetaData)
            ->offsetAddToArray(DatagridInterface::METADATA_REQUIRED_MODULES_KEY, ['oro/datafilter-builder']);
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
     * @param DatagridConfiguration $config
     *
     * @return FilterInterface[]
     */
    protected function getFiltersToApply(DatagridConfiguration $config)
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
     * @param DatagridConfiguration $config
     *
     * @return array
     */
    protected function getValuesToApply(DatagridConfiguration $config)
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
