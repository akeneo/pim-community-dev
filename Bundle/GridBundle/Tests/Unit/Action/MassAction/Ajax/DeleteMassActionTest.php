<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Ajax;

use Oro\Bundle\GridBundle\Action\MassAction\Ajax\DeleteMassAction;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\MassActionTestCase;

class DeleteMassActionTest extends MassActionTestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    protected function createMassAction(array $options)
    {
        return new DeleteMassAction($options);
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            'minimum set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'oro_grid.mass_action.handler.delete',
                    'frontend_type'    => 'ajax',
                    'route'            => 'oro_grid_mass_action',
                    'route_parameters' => array(),
                    'confirmation'     => true,
                ),
                'inputOptions' => array(
                    'name'    => 'ajaxAction',
                ),
            ),
            'full set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'test.delete.handler.service',
                    'frontend_type'    => 'ajax',
                    'route'            => 'my_custom_route',
                    'route_parameters' => array('key' => 'value'),
                    'confirmation'     => false,
                ),
                'inputOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'test.delete.handler.service',
                    'route'            => 'my_custom_route',
                    'route_parameters' => array('key' => 'value'),
                    'confirmation'     => false,
                ),
            )
        );
    }
}
