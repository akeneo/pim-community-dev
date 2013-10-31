<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\AbstractMassAction;

class AjaxMassAction extends AbstractMassAction
{
    /** @var array */
    protected $requiredOptions = ['handler'];

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        $options['frontend_type'] = 'ajax';

        if (empty($options['route'])) {
            $options['route'] = 'oro_datagrid_mass_action';
        }

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = [];
        }

        if (!isset($options['confirmation'])) {
            $options['confirmation'] = true;
        }

        parent::setOptions($options);

        return $this;
    }
}
