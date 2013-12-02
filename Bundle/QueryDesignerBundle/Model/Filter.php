<?php

namespace Oro\Bundle\QueryDesignerBundle\Model;

class Filter
{
    /**
     * @var string
     */
    protected $columnName;

    /**
     * @var string
     */
    protected $criterion;

    /**
     * Get column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set column name
     *
     * @param string $columnName
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
    }

    /**
     * Get filter criterion
     *
     * @return string
     */
    public function getCriterion()
    {
        return $this->criterion;
    }

    /**
     * Set filter criterion
     *
     * @param string $criterion
     */
    public function setCriterion($criterion)
    {
        $this->criterion = $criterion;
    }
}
