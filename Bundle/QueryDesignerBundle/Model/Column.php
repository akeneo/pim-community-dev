<?php

namespace Oro\Bundle\QueryDesignerBundle\Model;

class Column
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $sorting;

    /**
     * Get column name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set column name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get column label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set column label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get sorting mode.
     * Can be ASC, DESC or null.
     *
     * @return string|null
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Get sorting mode.
     * Can be ASC, DESC, empty string or null. The empty string or null are same and means no sorting.
     *
     * @param string|null $sorting
     */
    public function setSorting($sorting = null)
    {
        if ($sorting !== null && empty($sorting)) {
            $sorting = null;
        }
        $this->sorting = $sorting;
    }
}
