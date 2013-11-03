<?php

namespace Oro\Bundle\GridBundle\Action\MassAction\Redirect;

use Oro\Bundle\GridBundle\Action\MassAction\AbstractMassAction;

class RedirectMassAction extends AbstractMassAction
{
    /**
     * Required options: name, route
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options['frontend_type'] = 'redirect';

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = array();
        }

        parent::__construct($options);

        $this->assertRequiredOptions(array('route'));
    }
}
