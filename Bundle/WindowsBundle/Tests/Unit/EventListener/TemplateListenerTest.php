<?php

namespace Oro\Bundle\WindowsBundle\Tests\Unit\EventListener;

use Oro\Bundle\WindowsBundle\EventListener\TemplateListener;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GetResponseForControllerResultEvent
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var TemplateListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->listener = new TemplateListener($this->container);
    }


    public function testOnKernelViewNoContainer()
    {
        $request = Request::create('/test/url');
        $request->attributes = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');

        $request->attributes->expects($this->never())
            ->method('set');

        $this->event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->listener->onKernelView($this->event);
    }

    public function testOnKernelViewNoSeparator()
    {
        $template = 'test.twig.html';
        $request = Request::create('/test/url');
        $request->query->set('_widgetContainer', 'test');
        $request->attributes->set('_template', $template);

        $this->event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->listener->onKernelView($this->event);
        $this->assertEquals($template, $request->attributes->get('_template'));
    }

    /**
     * @dataProvider templateDataProvider
     * @param bool $containerExists
     * @param bool $widgetExists
     * @param string $expectedTemplate
     * @param string $requestAttribute
     */
    public function testOnKernelViewWidgetTemplateExists($containerExists, $widgetExists, $expectedTemplate, $requestAttribute)
    {
        $request = Request::create('/test/url');
        $request->$requestAttribute->set('_widgetContainer', 'container');
        $request->attributes->set('_template', 'TestBundle:Default:test.html.twig');

        $this->event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine')
            ->setMethods(array('exists'))
            ->disableOriginalConstructor()
            ->getMock();
        $templating->expects($this->any())
            ->method('exists')
            ->will(
                $this->returnValueMap(
                    array(
                        array('TestBundle:Default:container.test.html.twig', $containerExists),
                        array('TestBundle:Default:widget.test.html.twig', $widgetExists)
                    )
                )
            );

        $this->container->expects($this->any())
            ->method('get')
            ->with('templating')
            ->will($this->returnValue($templating));

        $this->listener->onKernelView($this->event);
        $this->assertEquals($expectedTemplate, $request->attributes->get('_template'));
    }

    /**
     * @return array
     */
    public function templateDataProvider()
    {
        return array(
            'container yes, widget yes' => array(true, true, 'TestBundle:Default:container.test.html.twig', 'query'),
            'container yes, widget no' => array(true, false, 'TestBundle:Default:container.test.html.twig', 'query'),
            'container no, widget yes' => array(false, true, 'TestBundle:Default:widget.test.html.twig', 'query'),
            'container no, widget no' => array(false, false, 'TestBundle:Default:test.html.twig', 'query'),
            'post container yes, widget yes' => array(true, true, 'TestBundle:Default:container.test.html.twig', 'request'),
            'post container yes, widget no' => array(true, false, 'TestBundle:Default:container.test.html.twig', 'request'),
            'post container no, widget yes' => array(false, true, 'TestBundle:Default:widget.test.html.twig', 'request'),
            'post container no, widget no' => array(false, false, 'TestBundle:Default:test.html.twig', 'request')
        );
    }
}
