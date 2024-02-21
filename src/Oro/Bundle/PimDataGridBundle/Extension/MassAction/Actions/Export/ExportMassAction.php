<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\Widget\WidgetMassAction;

/**
 * Mass export action
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassAction extends WidgetMassAction implements ExportMassActionInterface
{
    /** @var array $requiredOptions */
    protected $requiredOptions = ['route', 'frontend_type', 'handler', 'context'];

    /** @var array $requiredRouteParams */
    protected $requiredRouteParams = ['_format', '_contentType'];

    /**
     * {@inheritdoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        $options['frontend_type'] = 'export';

        if (empty($options['route'])) {
            $options['route'] = 'pim_datagrid_export_index';
        }

        if (empty($options['handler'])) {
            $options['handler'] = 'quick_export';
        }

        if (empty($options['context'])) {
            $options['context'] = [];
        }

        return parent::setOptions($options);
    }

    /**
     * Add method to assert required route parameters
     */
    protected function assertHasRequiredOptions()
    {
        parent::assertHasRequiredOptions();

        $this->assertRequiredRouteParameters();
    }

    /**
     * Check if route parameters are well defined
     *
     * @throws \LogicException
     */
    protected function assertRequiredRouteParameters()
    {
        foreach ($this->requiredRouteParams as $requiredRouteParam) {
            if (!isset($this->options['route_parameters'][$requiredRouteParam])) {
                throw new \LogicException(
                    sprintf(
                        'There is no route_parameter named "%s" for action "%s"',
                        $requiredRouteParam,
                        $this->getName()
                    )
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExportContext()
    {
        return $this->options['context'];
    }
}
