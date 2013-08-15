<?php

namespace Oro\Bundle\GridBundle\Action\MassAction\Widget;

class WindowMassAction extends WidgetMassAction
{
    /**
     * Required options: name, route
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options['frontend_type'] = 'dialog';

        parent::__construct($options);
    }
}
