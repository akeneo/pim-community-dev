<?php

namespace Oro\Bundle\GridBundle\Action\MassAction\Widget;

use Oro\Bundle\GridBundle\Action\MassAction\AbstractMassAction;

class WidgetMassAction extends AbstractMassAction
{
    /**
     * Required options: name, route, frontend_type
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = array();
        }

        if (empty($options['frontend_options'])) {
            $options['frontend_options'] = array();
        }

        parent::__construct($options);

        $this->assertRequiredOptions(array('route', 'frontend_type'));
    }
}
