<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\Controller;

use Pim\Bundle\CustomEntityBundle\Controller\Controller;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $request;
    protected $configurationRegistry;
    protected $controller;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationRegistry = $this->getMock('Pim\Bundle\CustomEntityBundle\Configuration\Registry');

        $this->controller = new Controller($this->request, $this->configurationRegistry);
    }

    /**
     * Test related method
     */
    public function testAction()
    {
        $controllerStrategy = $this
            ->getMockBuilder('Pim\Bundle\CustomEntityBundle\Controller\Strategy\strategyInterface')
            ->setMethods(['action'])
            ->getMock();

        $configuration = $this->getMock('Pim\Bundle\CustomEntityBundle\Configuration\ConfigurationInterface');
        $configuration->expects($this->once())
            ->method('getControllerStrategy')
            ->will($this->returnValue($controllerStrategy));

        $controllerStrategy->expects($this->once())
            ->method('action')
            ->with($this->identicalTo($configuration), $this->identicalTo($this->request))
            ->will($this->returnValue('success'));

        $this->configurationRegistry->expects($this->once())
            ->method('has')
            ->with($this->equalTo('name'))
            ->will($this->returnValue(true));

        $this->configurationRegistry->expects($this->once())
            ->method('get')
            ->with($this->equalTo('name'))
            ->will($this->returnValue($configuration));

        $this->assertEquals('success', $this->controller->action('name', 'action'));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testUnexistingConfig()
    {
        $this->configurationRegistry->expects($this->once())
            ->method('has')
            ->with($this->equalTo('name'))
            ->will($this->returnValue(false));
        $this->controller->action('name', 'action');
    }
}
