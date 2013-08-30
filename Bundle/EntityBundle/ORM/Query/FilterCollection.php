<?php

namespace Oro\Bundle\EntityBundle\ORM\Query;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection as BaseFilterCollection;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Collection class for all the query filters.
 * Modified copy of Doctrine\ORM\Query\FilterCollection. Extending is impossible because of private properties
 *
 */
class FilterCollection
{
    /* Filter STATES */
    /**
     * A filter object is in CLEAN state when it has no changed parameters.
     */
    const FILTERS_STATE_CLEAN  = 1;

    /**
     * A filter object is in DIRTY state when it has changed parameters.
     */
    const FILTERS_STATE_DIRTY = 2;

    /**
     * The used Configuration.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The EntityManager that "owns" this FilterCollection instance.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Instances of enabled filters.
     *
     * @var array
     */
    protected $enabledFilters = array();

    /**
     * Instances of disabled filters.
     *
     * @var array
     */
    protected $disabledFilters = array();

    /**
     * @var string The filter hash from the last time the query was parsed.
     */
    protected $filterHash;

    /**
     * @var integer $state The current state of this filter
     */
    protected $filtersState = self::FILTERS_STATE_CLEAN;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->config = $em->getConfiguration();
    }

    /**
     * Get all the enabled filters.
     *
     * @return array The enabled filters.
     */
    public function getEnabledFilters()
    {
        return $this->enabledFilters;
    }

    /**
     * Get all the disabled filters.
     *
     * @return array The disabled filters.
     */
    public function getDisabledFilters()
    {
        return $this->disabledFilters;
    }

    /**
     * Enables a filter from the collection.
     *
     * @param string $name Name of the filter.
     *
     * @throws \InvalidArgumentException If the filter does not exist.
     *
     * @return SQLFilter The enabled filter.
     */
    public function enable($name)
    {
        if (!isset($this->enabledFilters[$name]) && !isset($this->disabledFilters[$name])) {
            /**
             * Keeping logic of doctrine filters
             */
            if (null === $filterClass = $this->config->getFilterClassName($name)) {
                throw new \InvalidArgumentException("Filter '" . $name . "' does not exist.");
            }

            $this->enabledFilters[$name] = new $filterClass($this->em);
            $this->sortFilters();
        }

        if (!isset($this->enabledFilters[$name]) && isset($this->disabledFilters[$name])) {
            $this->enabledFilters[$name] = $this->disabledFilters[$name];
            unset($this->disabledFilters[$name]);
            $this->sortFilters();
        }

        return $this->enabledFilters[$name];
    }

    /**
     * Disables a filter.
     *
     * @param string $name Name of the filter.
     *
     * @return SQLFilter The disabled filter.
     *
     * @throws \InvalidArgumentException If the filter does not exist.
     */
    public function disable($name)
    {
        // Get the filter to return it
        $filter = $this->getFilter($name);
        //Saving filter in disabled filters for possible future uses
        $this->disabledFilters[$name] = $filter;

        unset($this->enabledFilters[$name]);

        // Now the filter collection is dirty
        $this->filtersState = self::FILTERS_STATE_DIRTY;

        return $filter;
    }

    /**
     * Get an enabled filter from the collection.
     *
     * @param string $name Name of the filter.
     *
     * @return SQLFilter The filter.
     *
     * @throws \InvalidArgumentException If the filter is not enabled.
     */
    public function getFilter($name)
    {
        if (!isset($this->enabledFilters[$name])) {
            throw new \InvalidArgumentException("Filter '" . $name . "' is not enabled.");
        }

        return $this->enabledFilters[$name];
    }

    /**
     * Adding filter, by default disabled collection is used
     *
     * @param string $name
     * @param SQLFilter $filter
     */
    public function addFilter($name, SQLFilter $filter)
    {
        $this->disabledFilters[$name] = $filter;
    }

    /**
     * @return boolean True, if the filter collection is clean.
     */
    public function isClean()
    {
        return self::FILTERS_STATE_CLEAN === $this->filtersState;
    }

    /**
     * Generates a string of currently enabled filters to use for the cache id.
     *
     * @return string
     */
    public function getHash()
    {
        // If there are only clean filters, the previous hash can be returned
        if (self::FILTERS_STATE_CLEAN === $this->filtersState) {
            return $this->filterHash;
        }

        $filterHash = '';
        foreach ($this->enabledFilters as $name => $filter) {
            $filterHash .= $name . $filter;
        }

        return $filterHash;
    }

    /**
     * Set the filter state to dirty.
     */
    public function setFiltersStateDirty()
    {
        $this->filtersState = self::FILTERS_STATE_DIRTY;
    }

    /**
     * Sort enabled filters
     */
    protected function sortFilters()
    {
        // Keep the enabled filters sorted for the hash
        ksort($this->enabledFilters);

        // Now the filter collection is dirty
        $this->filtersState = self::FILTERS_STATE_DIRTY;
    }
}

