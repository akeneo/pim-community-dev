<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

class MassActionMediator implements MassActionMediatorInterface
{
    /**
     * @var MassActionInterface
     */
    protected $massAction;

    /**
     * @var \Iterator|ResultRecordInterface[]
     */
    protected $results;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var DatagridInterface|null
     */
    protected $datagrid;

    /**
     * @param MassActionInterface $massAction
     * @param \Iterator|ResultRecordInterface[] $results
     * @param array $data
     * @param DatagridInterface $datagrid
     */
    public function __construct(
        MassActionInterface $massAction,
        $results,
        array $data = array(),
        DatagridInterface $datagrid
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
