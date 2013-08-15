<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

class DeleteMassAction extends AjaxMassAction
{
    /**
     * Set default delete parameters
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['name'])) {
            $options['name'] = 'delete';
        }

        if (empty($options['label'])) {
            $options['label'] = 'Delete';
        }

        if (empty($options['handler'])) {
            $options['handler'] = 'oro_grid.mass_action.handler.delete';
        }

        parent::__construct($options);
    }
}
