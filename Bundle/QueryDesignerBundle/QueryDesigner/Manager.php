<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\Provider\SystemAwareResolver;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;

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
        $filters         = $this->getFilters();
        foreach ($filters as $filter) {
            $filtersMetadata[] = $filter->getMetadata();
        }

        return ['filters' => $filtersMetadata];
    }

    /**
     * Add filter to array of available filters
     *
     * @param string          $type
     * @param FilterInterface $filter
     */
    public function addFilter($type, FilterInterface $filter)
    {
        $this->filters[$type] = $filter;
    }

    /**
     * Creates a new instance of a filter based on a configuration
     * of a filter registered in this manager with the given name
     *
     * @param string $name   A filter name
     * @param array  $params An additional parameters of a new filter
     * @throws \RuntimeException if a filter with the given name does not exist
     * @return FilterInterface
     */
    public function createFilter($name, array $params = null)
    {
        $config = null;
        $filtersConfig = $this->config->offsetGet('filters');
        foreach ($filtersConfig as $filterName => $attr) {
            if ($filterName === $name) {
                $config = $attr;
                break;
            }
        }
        if ($config === null) {
            throw new \RuntimeException(sprintf('Unknown filter "%s".', $name));
        }

        if ($params !== null && !empty($params)) {
            $config = array_merge($config, $params);
        }

        return $this->getFilterObject($name, $config);
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
