<?php

namespace Oro\Bundle\GridBundle\Datagrid\Views;

class View
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $filtersData = array();

    /** @var array */
    protected $sortersData = array();

    public function __construct($name, $filtersData = array(), $sortersData = array())
    {
        $this->name        = $name;
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
     * @param array $sortersData
     */
    public function setSortersData(array $sortersData)
    {
        $this->sortersData = $sortersData;
    }

    /**
     * @return array
     */
    public function getSortersData()
    {
        return $this->sortersData;
    }

    /**
     * @param array $filtersData
     */
    public function setFiltersData(array $filtersData)
    {
        $this->filtersData = $filtersData;
    }

    /**
     * @return array
     */
    public function getFiltersData()
    {
        return $this->filtersData;
    }
}
