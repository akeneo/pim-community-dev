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

        $translator = $this->getMock(
            'Oro\Bundle\TranslationBundle\Translation\Translator',
            array(),
            array(
                $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface'),
                $this->getMock('Symfony\Component\Translation\MessageSelector'),
            )
        );
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

        $controller = new Controller($translator, $templating, '', array());
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
