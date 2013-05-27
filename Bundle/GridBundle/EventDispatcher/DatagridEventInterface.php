<?php

namespace Oro\Bundle\GridBundle\EventDispatcher;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

interface DatagridEventInterface
{
    /**
     * Get datagrid instance
     *
     * @return DatagridInterface
     */
    public function getDatagrid();

    /**
     * Check whether datagrid has specified name
     *
     * @param string $datagridName
     * @return bool
     */
    public function isDatagridName($datagridName);
}
