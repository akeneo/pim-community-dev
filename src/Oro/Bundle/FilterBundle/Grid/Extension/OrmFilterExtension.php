<?php

namespace Oro\Bundle\FilterBundle\Grid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Translation\TranslatorInterface;

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
        $filters = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);

        if (!$filters) {
            return false;
        }

        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        // validate extension configuration
        $this->validateConfiguration(
            new Configuration(array_keys($this->filters)),
            ['filters' => $config->offsetGetByPath(Configuration::FILTERS_PATH)]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $filters = $this->getFiltersToApply($config);
        $values = $this->getValuesToApply($config);
        $datasourceAdapter = new OrmFilterDatasourceAdapter($datasource->getQueryBuilder());

        foreach ($filters as $filter) {
            $value = isset($values[$filter->getName()]) ? $values[$filter->getName()] : false;

            if ($value !== false) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $filter->apply($datasourceAdapter, $form->getData());
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        $filtersState = $data->offsetGetByPath('[state][filters]', []);
        $filtersMetaData = [];

        $filters = $this->getFiltersToApply($config);
        $values = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            $value = isset($values[$filter->getName()]) ? $values[$filter->getName()] : false;

            if ($value !== false) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $filtersState[$filter->getName()] = $value;
                }
            }

            $metadata = $filter->getMetadata();
            $filtersMetaData[] = array_merge(
                $metadata,
                ['label' => $this->translator->trans($metadata['label'])]
            );
        }

        $data->offsetAddToArray('state', ['filters' => $filtersState])
            ->offsetAddToArray('filters', $filtersMetaData);
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
        $filters = [];
        $filtersConfig = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        foreach ($filtersConfig as $column => $filter) {
            // if label not set, try to suggest it from column with the same name
            if (!isset($filter['label'])) {
                $filter['label'] = $config->offsetGetByPath(
                    sprintf('[%s][%s][label]', FormatterConfiguration::COLUMNS_KEY, $column)
                );
            }
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

        $filters = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        $defaultFilters = $config->offsetGetByPath(Configuration::DEFAULT_FILTERS_PATH, []);
        $filterBy = $this->requestParams->get(self::FILTER_ROOT_PARAM) ? : $defaultFilters;

        foreach ($filterBy as $column => $value) {
            if (isset($filters[$column])) {
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
        $type = $config[FilterUtility::TYPE_KEY];

        $filter = $this->filters[$type];
        $filter->init($name, $config);

        return clone $filter;
    }
}
