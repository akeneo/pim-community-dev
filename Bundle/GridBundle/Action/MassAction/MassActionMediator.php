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
     * @var Request
     */
    protected $request;

    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    /**
     * @param MassActionInterface $massAction
     * @param \Iterator|ResultRecordInterface[] $results
     * @param Request $request
     * @param DatagridInterface $datagrid
     */
    public function __construct(
        MassActionInterface $massAction,
        $results,
        Request $request,
        DatagridInterface $datagrid
    ) {
        $this->massAction = $massAction;
        $this->results    = $results;
        $this->request    = $request;
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
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getDatagrid()
    {
        return $this->datagrid;
    }
}
