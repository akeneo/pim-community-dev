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
        $configuration = new Configuration([]);
        $result = $processor->processConfiguration($configuration, [$options]);

        $this->assertEquals($expects, $result);
    }

    public function dataProviderConfigTree()
    {
        return [
            [
                [],
                [
                    'js_translation' => [
                        'domains' => ['jsmessages', 'validators'],
                        'debug'   => '%kernel.debug%',
                    ]
                ]
            ],
            [
                ['js_translation' => []],
                [
                    'js_translation' => [
                        'domains' => ['jsmessages', 'validators'],
                        'debug'   => '%kernel.debug%',
                    ]
                ]
            ],
            [
                [
                    'js_translation' => [
                        'domains' => ['validators'],
                        'debug'   => true,
                    ]
                ],
                [
                    'js_translation' => [
                        'domains' => ['validators'],
                        'debug'   => true,
                    ]
                ]
            ],
        ];
    }
}
