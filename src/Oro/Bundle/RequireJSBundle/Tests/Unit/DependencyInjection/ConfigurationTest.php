<?php

namespace Oro\Bundle\RequireJSBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\RequireJSBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderExceptionConfigTree
     */
    public function testExceptionConfigTree($options, $exception)
    {
        $this->setExpectedException($exception);

        $processor = new Processor();
        $configuration = new Configuration([]);
        $processor->processConfiguration($configuration, [$options]);
    }

    public function dataProviderExceptionConfigTree()
    {
        return [
            [
                [],
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ],
            [
                [
                    'js_engine' => 'node',
                    'config'    => ['waitSeconds' => -3],
                ],
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ],
            [
                [
                    'js_engine' => 'node',
                    'config'    => ['scriptType' => ''],
                ],
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ],
            [
                [
                    'js_engine'        => 'node',
                    'building_timeout' => -3,
                ],
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ],
            [
                [
                    'js_engine' => 'node',
                    'build'     => ['optimize' => 'test'],
                ],
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ],
        ];
    }

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
                [
                    'js_engine' => 'node'
                ],
                [
                    'js_engine' => 'node',
                    'config'    => [
                        'waitSeconds' => 0,
                    ],
                    'web_root'         => '%kernel.root_dir%/../web',
                    'build_path'       => 'js/app.min.js',
                    'building_timeout' => 60,
                    'build'            => [
                        'optimize' => 'uglify2',
                        'paths'    => [],
                    ]
                ]
            ],
            [
                [
                    'config' => [
                        'waitSeconds'   => 0,
                        'enforceDefine' => true,
                        'scriptType'    => 'text/javascript'
                    ],
                    'js_engine'        => 'node',
                    'build_path'       => 'js/test/app.min.js',
                    'building_timeout' => 3600,
                    'build'            => [
                        'optimize'                => 'none',
                        'generateSourceMaps'      => false,
                        'preserveLicenseComments' => true,
                        'useSourceUrl'            => false,
                        'paths'                   => [],
                    ]
                ],
                [
                    'config' => [
                        'waitSeconds'   => 0,
                        'enforceDefine' => true,
                        'scriptType'    => 'text/javascript',
                    ],
                    'js_engine'        => 'node',
                    'web_root'         => '%kernel.root_dir%/../web',
                    'build_path'       => 'js/test/app.min.js',
                    'building_timeout' => 3600,
                    'build'            => [
                        'optimize'                => 'none',
                        'generateSourceMaps'      => false,
                        'preserveLicenseComments' => 1,
                        'useSourceUrl'            => false,
                        'paths'                   => [],
                    ],
                ]
            ],
        ];
    }
}
