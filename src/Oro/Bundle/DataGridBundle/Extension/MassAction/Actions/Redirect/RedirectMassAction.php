<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\AbstractAction;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

class RedirectMassAction extends AbstractAction implements MassActionInterface
{
    /** @var array */
    protected $requiredOptions = ['route'];

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        $options['frontend_type'] = 'redirect';

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = [];
        }

        return parent::setOptions($options);
    }
}
