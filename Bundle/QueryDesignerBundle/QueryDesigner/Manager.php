<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\Provider\SystemAwareResolver;
use Oro\Bundle\FilterBundle\Filter\Orm\FilterInterface;

class Manager
{
    /** @var ConfigurationObject */
    protected $config;

    /** @var FilterInterface[] */
    protected $filters = [];

    /**
     * Constructor
     *
     * @param array               $config
     * @param SystemAwareResolver $resolver
     */
    public function __construct(
        array $config,
        SystemAwareResolver $resolver
    ) {
        $resolver->resolve($config);
        $this->config = ConfigurationObject::create($config['query-designer']);
    }

    /**
     * Returns metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        $filtersMetadata = [];
        $filters = $this->getFilters();
        foreach ($filters as $filter) {
            $filtersMetadata[] = $filter->getMetadata();
        }

        return ['filters' => $filtersMetadata];
    }

    /**
     * Add filter to array of available filters
     *
     * @param string          $filterType
     * @param FilterInterface $filter
     */
    public function addFilter($filterType, FilterInterface $filter)
    {
        $this->filters[$filterType] = $filter;
    }

    /**
     * Gets a filter
     *
     * @param $filterType
     * @return FilterInterface
     */
    public function getFilter($filterType)
    {
        return $this->filters[$filterType];
    }

    /**
     * Returns all available filters
     *
     * @return FilterInterface[]
     */
    protected function getFilters()
    {
        $filters       = [];
        $filtersConfig = $this->config->offsetGet('filters');
        foreach ($filtersConfig as $name => $attr) {
            $filters[$name] = $this->getFilterObject($name, $attr);
        }

        return $filters;
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
        $filter = clone $this->filters[$config['type']];
        $filter->init($name, $config);

        return $filter;
    }
}
