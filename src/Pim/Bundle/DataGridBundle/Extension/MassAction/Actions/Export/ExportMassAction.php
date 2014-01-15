<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Widget\WidgetMassAction;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

/**
 * Mass export action
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassAction extends WidgetMassAction
{
    /** @var array */
    protected $requiredOptions = ['route', 'frontend_type', 'handler'];

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        $options['handler']       = 'pim_datagrid.extension.mass_action.handler.export';
        $options['frontend_type'] = 'export';

        if (empty($options['route'])) {
            $options['route'] = 'oro_datagrid_mass_action';
        }

        return parent::setOptions($options);
    }
}
