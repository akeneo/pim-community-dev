<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\RedirectMassAction;

class EditMassAction extends RedirectMassAction
{
    /**
     * {@inheritdoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'pim_datagrid.extension.mass_action.handler.edit';
        }

        return parent::setOptions($options);
    }
}
