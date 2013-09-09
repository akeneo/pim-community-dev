<?php

namespace Oro\Bundle\GridBundle\Datagrid\Views;

class View
{
    /** @var string */
    protected $name;

    /** @var array */
    protected $filtersData;

    /** @var array */
    protected $sortersData;

    public function __construct($name, array $filtersData = array(), array $sortersData = array())
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
     *
     * @return $this
     */
    public function setSortersData(array $sortersData)
    {
        $this->sortersData = $sortersData;

        return $this;
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
     *
     * @return $this
     */
    public function setFiltersData(array $filtersData)
    {
        $this->filtersData = $filtersData;

        return $this;
    }

    /**
     * @return array
     */
    public function getFiltersData()
    {
        return $this->filtersData;
    }
}
