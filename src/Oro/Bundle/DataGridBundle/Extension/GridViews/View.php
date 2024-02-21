<?php

namespace Oro\Bundle\DataGridBundle\Extension\GridViews;

class View
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $filtersData;

    /** @var array */
    protected $sortersData;

    public function __construct($name, array $filtersData = [], array $sortersData = [])
    {
        $this->name = $name;
        $this->filtersData = $filtersData;
        $this->sortersData = $sortersData;
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for sorters data
     *
     * @param array $sortersData
     *
     * @return $this
     */
    public function setSortersData(array $sortersData)
    {
        $this->sortersData = $sortersData;

        return $this;
    }

    /**
     * Getter for sorters data
     *
     * @return array
     */
    public function getSortersData()
    {
        return $this->sortersData;
    }

    /**
     * Setter for filter data
     *
     * @param array $filtersData
     *
     * @return $this
     */
    public function setFiltersData(array $filtersData)
    {
        $this->filtersData = $filtersData;

        return $this;
    }

    /**
     * Getter for filter data
     *
     * @return array
     */
    public function getFiltersData()
    {
        return $this->filtersData;
    }

    /**
     * Convert to view data
     *
     * @return array
     */
    public function getMetadata()
    {
        return [
            'name'    => $this->getName(),
            'filters' => $this->getFiltersData(),
            'sorters' => $this->getSortersData()
        ];
    }
}
