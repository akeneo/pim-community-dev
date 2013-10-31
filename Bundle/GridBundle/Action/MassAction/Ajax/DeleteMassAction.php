<?php

namespace Oro\Bundle\GridBundle\Action\MassAction\Ajax;

class DeleteMassAction extends AjaxMassAction
{
    /**
     * Required options: name
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'oro_grid.mass_action.handler.delete';
        }

        parent::__construct($options);
    }
}
