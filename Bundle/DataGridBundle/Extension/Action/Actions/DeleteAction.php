<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

class DeleteAction extends AbstractAction
{
    /**
     * @var array
     */
    protected $requiredOptions = ['link'];

    /**
     * @param ActionConfiguration $options
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (!isset($options['confirmation'])) {
            $options['confirmation'] = true;
        }

        parent::setOptions($options);
    }
}
