<?php

namespace Oro\Bundle\QueryDesignerBundle\Model;

class Filter
{
    /**
     * @var string
     */
    protected $columnName;

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
}
