<?php

namespace Oro\Bundle\DataGridBundle\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;

interface GridEventInterface
{
    /**
     * Getter for datgrid
     *
     * @return DatagridInterface
     */
    public function getDatagrid();
}
