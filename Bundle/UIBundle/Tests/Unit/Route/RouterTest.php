<?php
namespace Oro\Bundle\UIBundle\Tests\Route;

use Oro\Bundle\UIBundle\Route\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $request;
    protected $route;

    /**
     * @var Router
     */
    protected $router;

    public function setUp()
    {
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->route = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->router = new Router($this->request, $this->route);
    }

    public function testActionRedirect()
    {
        $testUrl = 'test\\url\\index.html';

        $this->request->expects($this->once())
            ->method('get')
            ->will($this->returnValue(Router::ACTION_SAVE_AND_STAY));

        $this->route->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($testUrl));

        $redirect = $this->router->actionRedirect(
            array(
                'route' => 'test_route',
                'parameters' => array('id' => 1),
            ),
            array()
        );

        $this->assertEquals($testUrl, $redirect->getTargetUrl());
    }

    public function testSaveAndCloseActionRedirect()
    {
        $testUrl = 'save_and_close.html';

        $this->request->expects($this->once())
            ->method('get')
            ->will($this->returnValue('test'));

        $this->route->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($testUrl));
        /**
         * @var \Symfony\Component\HttpFoundation\RedirectResponse
         */
        $redirect = $this->router->actionRedirect(
            array(),
            array(
                'route' => 'test_route',
                'parameters' => array('id' => 1),
            )
        );

        $this->assertEquals($testUrl, $redirect->getTargetUrl());
    }

    public function testWrongParametersActionRedirect()
    {
        $this->setExpectedException('\LogicException');
        $this->router->actionRedirect(
            array(),
            array()
        );
    }
}
