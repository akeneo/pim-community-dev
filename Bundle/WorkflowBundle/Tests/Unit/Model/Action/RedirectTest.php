<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\Redirect;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\ItemStub;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    const REDIRECT_PATH = 'redirectUrl';

    /**
     * @var Redirect
     */
    protected $action;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->router->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(array($this, 'generateTestUrl')));

        $this->action = new Redirect(new ContextAccessor(), $this->router, self::REDIRECT_PATH);
    }

    protected function tearDown()
    {
        unset($this->router);
        unset($this->action);
    }

    /**
     * @param array $options
     * @dataProvider optionsDataProvider
     */
    public function testInitialize(array $options)
    {
        $this->action->initialize($options);
        $this->assertAttributeEquals($options, 'options', $this->action);
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return array(
            'route' => array(
                'options' => array(
                    'route' => 'test_route_name'
                ),
                'expectedUrl' => $this->generateTestUrl('test_route_name'),
            ),
            'route with parameters' => array(
                'options' => array(
                    'route' => 'test_route_name',
                    'route_parameters' => array('id' => 1),
                ),
                'expectedUrl' => $this->generateTestUrl('test_route_name', array('id' => 1)),
            ),
            'plain url' => array(
                'options' => array(
                    'url' => 'http://some.host/path'
                ),
                'expectedUrl' => 'http://some.host/path'
            ),
        );
    }

    /**
     * @param array $options
     * @param string $exceptionName
     * @param string $exceptionMessage
     * @dataProvider initializeExceptionDataProvider
     */
    public function testInitializeException(array $options, $exceptionName, $exceptionMessage)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->action->initialize($options);
    }

    /**
     * @return array
     */
    public function initializeExceptionDataProvider()
    {
        return array(
            'no name' => array(
                'options' => array(),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Either url or route parameter must be specified',
            ),
            'invalid route parameters' => array(
                'options' => array(
                    'route' => 'test_route_name',
                    'route_parameters' => 'stringData',
                ),
                'exceptionName' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'exceptionMessage' => 'Route parameters must be an array',
            ),
        );
    }

    /**
     * @param array $options
     * @param string $expectedUrl
     * @dataProvider optionsDataProvider
     */
    public function testExecute(array $options, $expectedUrl)
    {
        if (!empty($options['route'])) {

        }

        $context = new ItemStub();

        $this->action->initialize($options);
        $this->action->execute($context);

        $urlProperty = self::REDIRECT_PATH;
        $this->assertEquals($expectedUrl, $context->$urlProperty);
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return string
     */
    public function generateTestUrl($routeName, array $routeParameters = array())
    {
        $url = 'url:' . $routeName;
        if ($routeParameters) {
            $url .= ':' . serialize($routeParameters);
        }

        return $url;
    }
}
