<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

class DefaultMassAction extends AbstractMassAction
{
    /**
     * Set default route
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['route'])) {
            $options['route'] = 'oro_grid_mass_action';
        }

        parent::__construct($options);
    }
}
