<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

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
     * @var DatagridInterface|null
     */
    protected $datagrid;

    /**
     * @param MassActionInterface $massAction
     * @param \Iterator|ResultRecordInterface[] $results
     * @param DatagridInterface|null $datagrid
     */
    public function __construct(MassActionInterface $massAction, $results, DatagridInterface $datagrid = null)
    {
        $this->massAction = $massAction;
        $this->results    = $results;
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
    public function getDatagrid()
    {
        return $this->datagrid;
    }
}
