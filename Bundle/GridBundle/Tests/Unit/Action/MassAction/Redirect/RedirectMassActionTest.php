<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Redirect;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Action\MassAction\Redirect\RedirectMassAction;
use Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\MassActionWithExceptionsTestCase;

class RedirectMassActionTest extends MassActionWithExceptionsTestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    protected function createMassAction(array $options)
    {
        return new RedirectMassAction($options);
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            'minimum set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'redirectAction',
                    'frontend_type'    => 'redirect',
                    'route'            => 'some_route',
                    'route_parameters' => array(),
                ),
                'inputOptions' => array(
                    'name'    => 'redirectAction',
                    'route'   => 'some_route',
                ),
            ),
            'full set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'redirectAction',
                    'frontend_type'    => 'redirect',
                    'route'            => 'some_route',
                    'route_parameters' => array('key' => 'value'),
                ),
                'inputOptions' => array(
                    'name'             => 'redirectAction',
                    'route'            => 'some_route',
                    'route_parameters' => array('key' => 'value'),
                ),
            )
        );
    }

    /**
     * @return array
     */
    public function constructExceptionDataProvider()
    {
        return array(
            'no name option' => array(
                'exceptionName' => '\InvalidArgumentException',
                'exceptionMessage' =>
                'Option "name" is required for mass action class ' .
                'Oro\Bundle\GridBundle\Action\MassAction\Redirect\RedirectMassAction',
                'inputOptions' => array(),
            ),
            'no route option' => array(
                'exceptionName' => '\InvalidArgumentException',
                'exceptionMessage' => 'Option "route" is required for mass action "redirectAction"',
                'inputOptions' => array(
                    'name' => 'redirectAction'
                ),
            ),
        );
    }
}
