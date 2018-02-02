<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\SequentialEdit;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\AbstractAction;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;

/**
 * Sequential edit action
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditAction extends AbstractAction implements MassActionInterface
{
    /** @var array $requiredOptions */
    protected $requiredOptions = [];

    /** @var array $requiredRouteParams */
    protected $requiredRouteParams = [];

    /**
     * {@inheritdoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        $options['frontend_type'] = 'sequential_edit';

        if (empty($options['route'])) {
            $options['route'] = 'pim_enrich_mass_edit_action_sequential_edit';
        }

        if (empty($options['handler'])) {
            $options['handler'] = 'sequential_edit';
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
