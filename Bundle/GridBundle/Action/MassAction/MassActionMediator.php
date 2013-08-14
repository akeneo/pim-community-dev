<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;

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
     * @param MassActionInterface $massAction
     * @param \Iterator|ResultRecordInterface[] $results
     */
    public function __construct(MassActionInterface $massAction, $results)
    {
        $this->massAction = $massAction;
        $this->results    = $results;
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
}
