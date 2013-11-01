<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

class MassActionMediator implements MassActionMediatorInterface
{
    /** @var MassActionInterface */
    protected $massAction;

    /** @var \Iterator */
    protected $results;

    /** @var array */
    protected $data;

    /** @var DatagridInterface|null */
    protected $datagrid;

    /**
     * @param MassActionInterface     $massAction
     * @param DatagridInterface       $datagrid
     * @param IterableResultInterface $results
     * @param array                   $data
     */
    public function __construct(
        MassActionInterface $massAction,
        DatagridInterface $datagrid,
        IterableResultInterface $results,
        array $data = []
    ) {
        $this->massAction = $massAction;
        $this->results    = $results;
        $this->data       = $data;
        $this->datagrid   = $datagrid;
    }

    /**
     * {@inheritDoc}
     */
    public function getMassAction()
    {
        return $this->massAction;
    }

    /**
     * {@inheritDoc}
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }
}
