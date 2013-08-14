<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

interface MassActionMediatorInterface
{
    /**
     * @return MassActionInterface
     */
    public function getMassAction();

    /**
     * @return \Iterator|ResultRecordInterface[]
     */
    public function getResults();

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @return DatagridInterface|null
     */
    public function getDatagrid();
}
