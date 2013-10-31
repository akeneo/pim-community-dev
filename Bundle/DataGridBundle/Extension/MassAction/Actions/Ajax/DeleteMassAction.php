<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteMassAction extends AjaxMassAction
{
    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'oro_datagrid.extension.mass_action.handler.delete';
        }

        return parent::setOptions($options);
    }
}
