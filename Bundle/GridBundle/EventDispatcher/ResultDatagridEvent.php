<?php

namespace Oro\Bundle\GridBundle\EventDispatcher;

class ResultDatagridEvent extends AbstractDatagridEvent
{
    const NAME = 'oro_grid.datagrid.result';

    /**
     * @var array
     */
    protected $rows = array();

    /**
     * @param array $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }
}
