<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

interface MassActionHandlerInterface
{
    /**
     * Handle mass action
     *
     * @param MassActionMediatorInterface $mediator
     *
     * @return
     */
    public function handle(MassActionMediatorInterface $mediator);
}
