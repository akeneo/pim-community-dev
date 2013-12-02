<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

interface MassActionMediatorInterface
{
    /**
     * @return MassActionInterface
     */
    public function getMassAction();

    /**
     * @return IterableResultInterface
     */
    public function getResults();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return DatagridInterface
     */
    public function getDatagrid();
}
