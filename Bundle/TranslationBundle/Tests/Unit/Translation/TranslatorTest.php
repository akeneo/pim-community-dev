<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Translation;

use Oro\Bundle\TranslationBundle\Translation\Translator;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\MessageCatalogue;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $messages = array(
        'fr' => array(
            'jsmessages' => array(
                'foo' => 'foo (FR)',
            ),
            'messages' => array(
                'foo' => 'foo messages (FR)',
            ),
        ),
        'en' => array(
            'jsmessages' => array(
                'foo' => 'foo (EN)',
                'bar' => 'bar (EN)',
            ),
            'messages' => array(
                'foo' => 'foo messages (EN)',
            ),
            'validators' => array(
                'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
            ),
        ),
        'es' => array(
            'jsmessages' => array(
                'foobar' => 'foobar (ES)',
            ),
            'messages' => array(
                'foo' => 'foo messages (ES)',
            ),
        ),
        'pt-PT' => array(
            'jsmessages' => array(
                'foobarfoo' => 'foobarfoo (PT-PT)',
            ),
        ),
        'pt_BR' => array(
            'validators' => array(
                'other choice' =>
                    '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
            ),
        ),
    );

    /**
     * @dataProvider dataProviderGetTranslations
     */
    public function testGetTranslations($locale, $expected)
    {
        $locales = array_keys($this->messages);
        $translator = $this->getTranslator($this->getLoader());
        $_locale = !is_null($locale) ? $locale : reset($locales);
        $translator->setLocale($_locale);
        $translator->setFallbackLocales(array_slice($locales, array_search($_locale, $locales) + 1));
        $result = $translator->getTranslations(array('jsmessages', 'validators'), $locale);

        $this->assertEquals($expected, $result);
    }

    public function dataProviderGetTranslations()
    {
        return array(
            array(
                null,
                array(
                    'validators' => array(
                        'other choice' =>
                        '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                        'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
                    ),
                    'jsmessages' => array(
                        'foobarfoo' => 'foobarfoo (PT-PT)',
                        'foobar' => 'foobar (ES)',
                        'foo' => 'foo (FR)',
                        'bar' => 'bar (EN)',
                    ),
                )
            ),
            array(
                'fr',
                array(
                    'validators' => array(
                        'other choice' =>
                            '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                        'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
                    ),
                    'jsmessages' => array(
                        'foobarfoo' => 'foobarfoo (PT-PT)',
                        'foobar' => 'foobar (ES)',
                        'foo' => 'foo (FR)',
                        'bar' => 'bar (EN)',
                    ),
                )
            ),
            array(
                'en',
                array(
                    'validators' => array(
                        'other choice' =>
                            '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                        'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
                    ),
                    'jsmessages' => array(
                        'foobarfoo' => 'foobarfoo (PT-PT)',
                        'foobar' => 'foobar (ES)',
                        'foo' => 'foo (EN)',
                        'bar' => 'bar (EN)',
                    ),
                )
            ),
            array(
                'es',
                array(
                    'validators' => array(
                        'other choice' =>
                            '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                    ),
                    'jsmessages' => array(
                        'foobarfoo' => 'foobarfoo (PT-PT)',
                        'foobar' => 'foobar (ES)',
                    ),
                )
            ),
            array(
                'pt-PT',
                array(
                    'validators' => array(
                        'other choice' =>
                            '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                    ),
                    'jsmessages' => array(
                        'foobarfoo' => 'foobarfoo (PT-PT)',
                    ),
                )
            ),
            array(
                'pt_BR',
                array(
                    'validators' => array(
                        'other choice' =>
                            '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
                    ),
                )
            ),
        );
    }

    /**
     * Create a catalog and fills it in with messages
     *
     * @param string $locale
     * @param array $dictionary
     * @return MessageCatalogue
     */
    public function getCatalogue($locale, $dictionary)
    {
        $catalogue = new MessageCatalogue($locale);
        foreach ($dictionary as $domain => $messages) {
            foreach ($messages as $key => $translation) {
                $catalogue->set($key, $translation, $domain);
            }
        }
        return $catalogue;
    }

    /**
     * Creates a mock of Loader
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoader()
    {
        $messages = $this->messages;
        $obj = $this;
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader
            ->expects($this->any())
            ->method('load')
            ->will(
                $this->returnCallback(
                    function () use ($obj, $messages) {
                        $locale = func_get_arg(1);
                        return $obj->getCatalogue($locale, $messages[$locale]);
                    }
                )
            );
        return $loader;
    }

    /**
     * Creates a mock of Container
     *
     * @param LoaderInterface $loader
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainer($loader)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($loader));
        return $container;
    }

    /**
     * Creates instance of Translator
     *
     * @param $loader
     * @param array $options
     * @return Translator
     */
    public function getTranslator($loader, $options = array())
    {
        $translator = new Translator(
            $this->getContainer($loader),
            new MessageSelector(),
            array('loader' => array('loader')),
            $options
        );

        $translator->addResource('loader', 'foo', 'fr');
        $translator->addResource('loader', 'foo', 'en');
        $translator->addResource('loader', 'foo', 'es');
        $translator->addResource('loader', 'foo', 'pt-PT'); // European Portuguese
        $translator->addResource('loader', 'foo', 'pt_BR'); // Brazilian Portuguese

        return $translator;
    }
}
