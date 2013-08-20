<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponseInterface;

interface MassActionHandlerInterface
{
    /**
     * Handle mass action
     *
     * @param MassActionMediatorInterface $mediator
     * @return MassActionResponseInterface
     */
    public function handle(MassActionMediatorInterface $mediator);
}
