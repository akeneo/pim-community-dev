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

    public function __construct(string $name, array $filtersData = [], array $sortersData = [])
    {
        $this->name = $name;
        $this->filtersData = $filtersData;
        $this->sortersData = $sortersData;
    }

    /**
     * Getter for name
     */
    public function getName(): string
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
    public function setSortersData(array $sortersData): self
    {
        $this->sortersData = $sortersData;

        return $this;
    }

    /**
     * Getter for sorters data
     */
    public function getSortersData(): array
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
    public function setFiltersData(array $filtersData): self
    {
        $this->filtersData = $filtersData;

        return $this;
    }

    /**
     * Getter for filter data
     */
    public function getFiltersData(): array
    {
        return $this->filtersData;
    }

    /**
     * Convert to view data
     */
    public function getMetadata(): array
    {
        return [
            'name'    => $this->getName(),
            'filters' => $this->getFiltersData(),
            'sorters' => $this->getSortersData()
        ];
    }
}
