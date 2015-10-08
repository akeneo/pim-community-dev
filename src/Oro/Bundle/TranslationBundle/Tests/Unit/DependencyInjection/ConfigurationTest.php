<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TranslationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderConfigTree
     */
    public function testConfigTree($options, $expects)
    {
        $processor = new Processor();
        $configuration = new Configuration(array());
        $result = $processor->processConfiguration($configuration, array($options));

        $this->assertEquals($expects, $result);
    }

    public function dataProviderConfigTree()
    {
        return array(
            array(
                array(),
                array(
                    'js_translation' => array(
                        'domains' => array('jsmessages', 'validators'),
                        'debug' => '%kernel.debug%',
                    )
                )
            ),
            array(
                array('js_translation' => array()),
                array(
                    'js_translation' => array(
                        'domains' => array('jsmessages', 'validators'),
                        'debug' => '%kernel.debug%',
                    )
                )
            ),
            array(
                array(
                    'js_translation' => array(
                        'domains' => array('validators'),
                        'debug' => true,
                    )
                ),
                array(
                    'js_translation' => array(
                        'domains' => array('validators'),
                        'debug' => true,
                    )
                )
            ),
        );
    }
}
