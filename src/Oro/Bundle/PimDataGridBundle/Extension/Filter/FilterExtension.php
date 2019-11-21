<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Filter;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceAdapterResolverInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceTypes;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Filter extension, storage agnostic
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterExtension extends AbstractExtension
{
    /** @staticvar string Query param */
    const FILTER_ROOT_PARAM = '_filter';

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var DatasourceAdapterResolverInterface
     */
    protected $adapterResolver;

    public function __construct(
        RequestParameters $requestParams,
        TranslatorInterface $translator,
        DatasourceAdapterResolverInterface $adapterResolver
    ) {
        parent::__construct($requestParams);

        $this->translator = $translator;
        $this->adapterResolver = $adapterResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $filters = $config->offsetGetByPath(Configuration::COLUMNS_PATH, []);

        if (!$filters) {
            return false;
        }

        // ORO grids have a datasource of type ORM, do not apply our filters on these grids
        return DatasourceTypes::DATASOURCE_ORO !== $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $filters = $this->getFiltersToApply($config);
        $values = $this->getValuesToApply($config);
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);
        $adapterClass = $this->adapterResolver->getAdapterClass($datasourceType);
        $datasourceAdapter = new $adapterClass($datasource);

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
     * {@inheritdoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataIterableObject $data)
    {
        $filtersState = $data->offsetGetByPath('[state][filters]', []);
        $filtersConfig = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
        $filtersMetaData = [];

        $filters = $this->getFiltersToApply($config);
        $values = $this->getValuesToApply($config);

        foreach ($filters as $filter) {
            $value = isset($values[$filter->getName()]) ? $values[$filter->getName()] : false;

            if (false !== $value) {
                $form = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($value);
                }

                if ($form->isValid()) {
                    $filtersState[$filter->getName()] = $value;
                }
            }

            if (isset($filtersConfig[$filter->getName()])) {
                $metadata = $filter->getMetadata();

                $filtersMetaData[] = array_merge(
                    $metadata,
                    ['label' => $this->translator->trans($metadata['label'])]
                );
            }
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
            if (isset($filter['supported']) && is_array($filter['supported']['eq'])) {
                $comparison = $filter['supported']['eq'];
                if ($comparison[0] !== $comparison[1]) {
                    continue;
                }
            }

            // if label not set, try to suggest it from column with the same name
            if (!isset($filter['label'])) {
                $filter['label'] = $config->offsetGetByPath(
                    sprintf('[%s][%s][label]', FormatterConfiguration::COLUMNS_KEY, $column)
                );
            }
            $filters[] = $this->getFilterObject((string)$column, $filter);
        }

        // TODO: Try to make filter without views, to remove this kind of stuff
        $gridName = $config->offsetGetByPath('[name]');
        $gridCategoryConfig = $this->getCategoryFilterConfig($gridName);

        if (!isset($filtersConfig['category']) && null !== $gridCategoryConfig) {
            $filters[] = $this->getFilterObject('category', $gridCategoryConfig);
        }

        return $filters;
    }

    /**
     * Return the category filter config for the given $gridName,
     * if this $gridName is not filterable by category, return null.
     *
     * @param string $gridName
     *
     * @return array|null
     */
    protected function getCategoryFilterConfig($gridName)
    {
        $gridConfigs = [
            'product-grid' => [
                'type'      => 'product_category',
                'data_name' => 'category'
            ],
            'association-product-picker-grid' => [
                'type'      => 'product_category',
                'data_name' => 'category'
            ]
        ];

        return isset($gridConfigs[$gridName]) ? $gridConfigs[$gridName] : null;
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
        $filterBy = $this->requestParams->get(self::FILTER_ROOT_PARAM) ?: $defaultFilters;

        foreach ($filterBy as $column => $value) {
            if (isset($filters[$column]) || 'category' === $column) {
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
