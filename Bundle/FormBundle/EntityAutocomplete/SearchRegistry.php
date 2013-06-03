<?php

namespace Oro\Bundle\FormBundle\EntityAutocomplete;

class SearchRegistry
{
    /**
     * @var SearchFactoryInterface[]
     */
    protected $factories;

    /**
     * @var
     */
    protected $config = array();

    /**
     * @var SearchHandlerInterface[]
     */
    protected $cache = array();

    /**
     * @param array $config
     * @param SearchFactoryInterface[] $factories
     */
    public function __construct(array $config, array $factories = array())
    {
        $this->config = $config;
        $this->factories = $factories;
    }

    /**
     * @param string $type
     * @param SearchFactoryInterface $factory
     */
    public function addSearchFactory($type, SearchFactoryInterface $factory)
    {
        $this->factories[$type] = $factory;
    }

    /**
     * Gets instance of search handler by $type
     *
     * @param string $type
     * @return SearchHandlerInterface
     * @throws \RuntimeException When can't get search handler
     */
    public function getSearchHandler($type)
    {
        if (!isset($this->cache[$type])) {
            $this->cache[$type] = $this->createSearchHandler($type);
        }
        return $this->cache[$type];
    }

    /**
     * Creates instance of search handler by $type using config and factories
     *
     * @param string $type
     * @return SearchHandlerInterface
     * @throws \RuntimeException When can't create search handler
     */
    protected function createSearchHandler($type)
    {
        $config = $this->getAutocompleteConfig($type);

        $searchHandlerType = $config['type'];
        if (!isset($this->factories[$searchHandlerType])) {
            throw new \RuntimeException("Autocomplete search handler type \"$searchHandlerType\" is not registered");
        }

        return $this->factories[$searchHandlerType]->create($config);
    }

    /**
     * Get config of autocomplete by $type
     *
     * @param string $type
     * @return array
     * @throws \RuntimeException
     */
    protected function getAutocompleteConfig($type)
    {
        if (!isset($this->config[$type])) {
            throw new \RuntimeException("Autocomplete config \"$type\" is not defined");
        }
        return $this->config[$type];
    }
}
