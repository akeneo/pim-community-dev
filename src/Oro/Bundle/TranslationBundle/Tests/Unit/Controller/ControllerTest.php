<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Controller;

use Oro\Bundle\TranslationBundle\Controller\Controller;
use Oro\Bundle\TranslationBundle\Translation\Translator;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $translations = [
        'jsmessages' => [
            'foo' => 'Foo',
            'bar' => 'Bar',
        ],
        'validators' => [
            'int'    => 'Integer',
            'string' => 'string',
        ],
    ];

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Please provide valid twig template as third argument
     */
    public function testConstructor()
    {
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->getMockForAbstractClass();
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        new Controller($translator, $templating, '', []);
    }

    public function testIndexAction()
    {
        $content = 'CONTENT';
        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')
            ->getMockForAbstractClass();
        $templating->expects($this->once())
            ->method('render')
            ->will($this->returnValue($content));
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator->expects($this->once())
            ->method('getTranslations')
            ->will($this->returnValue([]));
        $controller = new Controller(
            $translator,
            $templating,
            'OroTranslationBundle:Translation:translation.js.twig',
            []
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
        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
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

        $translator = $this->getMockBuilder(Translator::class)
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
            []
        );
        $result = call_user_func_array([$controller, 'renderJsTranslationContent'], $params);

        $this->assertEquals($expected, $result);
    }

    public function dataProviderRenderJsTranslationContent()
    {
        return [
            [
                [['jsmessages', 'validators'], 'fr'],
                [
                    'locale'         => 'fr',
                    'defaultDomains' => ['jsmessages', 'validators'],
                    'messages'       => [
                        'jsmessages:foo'    => 'Foo',
                        'jsmessages:bar'    => 'Bar',
                        'validators:int'    => 'Integer',
                        'validators:string' => 'string',
                    ],
                ],
            ],
            [
                [['validators'], 'en', true],
                [
                    'locale'         => 'en',
                    'defaultDomains' => ['validators'],
                    'messages'       => [
                        'validators:int'    => 'Integer',
                        'validators:string' => 'string',
                    ],
                    'debug' => true,
                ],
            ],
            [
                [[], 'ch', false],
                [
                    'locale'         => 'ch',
                    'defaultDomains' => [],
                    'messages'       => [],
                ],
            ],
        ];
    }
}
