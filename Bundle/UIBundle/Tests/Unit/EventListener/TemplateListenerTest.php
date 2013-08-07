<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\UIBundle\EventListener\TemplateListener;

class TemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
     * @param mixed $inputTemplate
     * @param string $expectedTemplate
     * @param string $requestAttribute
     */
    public function testOnKernelViewWidgetTemplateExists(
        $containerExists,
        $widgetExists,
        $inputTemplate,
        $expectedTemplate,
        $requestAttribute
    ) {
        $request = Request::create('/test/url');
        $request->$requestAttribute->set('_widgetContainer', 'container');
        $request->attributes->set('_template', $inputTemplate);

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
                        array($this->getTemplateLogicalName('container'), $containerExists),
                        array($this->getTemplateLogicalName('widget'), $widgetExists)
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
        $inputTemplate     = $this->getTemplateLogicalName();
        $containerTemplate = $this->getTemplateLogicalName('container');
        $widgetTemplate    = $this->getTemplateLogicalName('widget');

        $templateReference = $this->getTemplateReference($inputTemplate);

        return array(
            'container yes, widget yes' => array(true, true, $inputTemplate, $containerTemplate, 'query'),
            'container yes, widget no' => array(true, false, $inputTemplate, $containerTemplate, 'query'),
            'container no, widget yes' => array(false, true, $inputTemplate, $widgetTemplate, 'query'),
            'container no, widget no' => array(false, false, $inputTemplate, $inputTemplate, 'query'),
            'post container yes, widget yes' => array(true, true, $inputTemplate, $containerTemplate, 'request'),
            'post container yes, widget no' => array(true, false, $inputTemplate, $containerTemplate, 'request'),
            'post container no, widget yes' => array(false, true, $inputTemplate, $widgetTemplate, 'request'),
            'post container no, widget no' => array(false, false, $inputTemplate, $inputTemplate, 'request'),
            'template reference' => array(true, false, $templateReference, $containerTemplate, 'query'),
        );
    }

    /**
     * @param string|null $container
     * @return string
     */
    protected function getTemplateLogicalName($container = null)
    {
        if ($container) {
            $container .= '.';
        }

        return 'TestBundle:Default:' . $container . 'test.html.twig';
    }

    /**
     * @param string $templateLogicalName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTemplateReference($templateLogicalName)
    {
        $templateReference = $this->getMockBuilder('Symfony\Component\Templating\TemplateReferenceInterface')
            ->disableOriginalConstructor()
            ->setMethods('getLogicalName')
            ->getMockForAbstractClass();
        $templateReference->expects($this->any())
            ->method('getLogicalName')
            ->will($this->returnValue($templateLogicalName));

        return $templateReference;
    }
}
