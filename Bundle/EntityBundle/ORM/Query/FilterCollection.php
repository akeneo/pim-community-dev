<?php

namespace Oro\Bundle\EntityBundle\ORM\Query;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection as BaseFilterCollection;

class FilterCollection extends BaseFilterCollection
{
    /**
     * Instances of enabled filters.
     *
     * @var array
     */
    private $enabledFilters = array();

    /**
     * Instances of disabled filters.
     *
     * @var array
     */
    private $disabledFilters = array();

    /**
     * @var string The filter hash from the last time the query was parsed.
     */
    private $filterHash;

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
            return parent::enable($name);
        }

        if (!isset($this->enabledFilters[$name]) && isset($this->disabledFilters[$name])) {
            $this->enabledFilters[$name] = $this->disabledFilters[$name];
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
        $filter = $this->getFilter($name);
        $this->disabledFilters[$name] = $filter;
        parent::disable($name);
    }

    public function addFilter($name, $filter)
    {
        $this->disabledFilters[$name] = $filter;
    }
}
