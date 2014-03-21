<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\AjaxMassAction;

/**
 * Override delete mass action to redefine default parameters
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteMassAction extends AjaxMassAction
{
    /**
     * @var array $requiredOptions
     */
    protected $requiredOptions = ['handler', 'entity_name'];

    /**
     * {@inheritdoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'mass_delete';
        }

        if (empty($options['route'])) {
            $options['route'] = 'pim_datagrid_mass_action';
        }

        return parent::setOptions($options);
    }
}
