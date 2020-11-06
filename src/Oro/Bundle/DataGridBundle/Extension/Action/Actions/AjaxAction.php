<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
class AjaxAction extends AbstractAction
{
    /**
     * @var array
     */
    protected $requiredOptions = ['link'];

    /**
     * @return array
     */
    public function getOptions(): ActionConfiguration
    {
        $options = parent::getOptions();

        $options['frontend_type'] = 'ajax';

        return $options;
    }
}
