<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Controller;

use Oro\Bundle\TranslationBundle\Controller\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $translations = array(
        'jsmessages' => array(
            'foo' => 'Foo',
            'bar' => 'Bar',
        ),
        'validators' => array(
            'int' => 'Integer',
            'string' => 'string',
        ),
    );

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide valid twig template as third argument
     */
    public function testConstructor()
    {
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->getMockForAbstractClass();
        $translator = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();
        new Controller($translator, $templating, '', array());
    }

    public function testIndexAction()
    {
        $content = 'CONTENT';
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->getMockForAbstractClass();
        $templating->expects($this->once())
            ->method('render')
            ->will($this->returnValue($content));
        $translator = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects($this->once())
            ->method('getTranslations')
            ->will($this->returnValue(array()));
        $controller = new Controller(
            $translator,
            $templating,
            'OroTranslationBundle:Translation:translation.js.twig',
            array()
        );

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getMimeType')
            ->with('js')
            ->will($this->returnValue('JS'));
        $response = $controller->indexAction($request, 'en');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('JS', $response->headers->get('Content-Type'));
    }

    /**
     * @dataProvider dataProviderRenderJsTranslationContent
     */
    public function testRenderJsTranslationContent($params, $expected)
    {
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating
            ->expects($this->any())
            ->method('render')
            ->will(
                $this->returnCallback(
                    function () {
                        $params = func_get_arg(1);
                        return $params['json'];
                    }
                )
            );

        $translator = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $translations = $this->translations;
        $translator
            ->expects($this->any())
            ->method('getTranslations')
            ->will(
                $this->returnCallback(
                    function ($domains) use ($translations) {
                        return array_intersect_key($translations, array_flip($domains));
                    }
                )
            );

        $controller = new Controller(
            $translator,
            $templating,
            'OroTranslationBundle:Translation:translation.js.twig',
            array()
        );
        $result = call_user_func_array(array($controller, 'renderJsTranslationContent'), $params);

        $this->assertEquals($expected, $result);
    }

    public function dataProviderRenderJsTranslationContent()
    {
        return array(
            array(
                array(array('jsmessages', 'validators'), 'fr'),
                array(
                    'locale' => 'fr',
                    'defaultDomains' => array('jsmessages', 'validators'),
                    'messages' => array(
                        'jsmessages:foo' => 'Foo',
                        'jsmessages:bar' => 'Bar',
                        'validators:int' => 'Integer',
                        'validators:string' => 'string',
                    ),
                ),
            ),
            array(
                array(array('validators'), 'en', true),
                array(
                    'locale' => 'en',
                    'defaultDomains' => array('validators'),
                    'messages' => array(
                        'validators:int' => 'Integer',
                        'validators:string' => 'string',
                    ),
                    'debug' => true,
                ),
            ),
            array(
                array(array(), 'ch', false),
                array(
                    'locale' => 'ch',
                    'defaultDomains' => array(),
                    'messages' => array(),
                ),
            ),
        );
    }
}
