<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

class AjaxMassAction extends AbstractMassAction
{
    /**
     * Set default route
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options['frontend_type'] = 'ajax';

        if (empty($options['route'])) {
            $options['route'] = 'oro_grid_mass_action';
        }

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = array();
        }

        parent::__construct($options);
    }
}
