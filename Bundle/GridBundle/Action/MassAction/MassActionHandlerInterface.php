<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface;

interface MassActionHandlerInterface
{
    /**
     * Handle mass action
     *
     * @param MassActionMediatorInterface $mediator
     * @return boolean
     */
    public function handle(MassActionMediatorInterface $mediator);
}
