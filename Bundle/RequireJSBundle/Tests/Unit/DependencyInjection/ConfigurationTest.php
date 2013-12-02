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
        $configuration = new Configuration(array());
        $processor->processConfiguration($configuration, array($options));
    }

    public function dataProviderExceptionConfigTree()
    {
        return array(
            array(
                array(
                    'js_engine' => 'node',
                    'config' => array('waitSeconds' => -3),
                ),
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array(
                    'js_engine' => 'node',
                    'config' => array('scriptType' => ''),
                ),
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array(
                    'js_engine' => 'node',
                    'building_timeout' => -3,
                ),
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
            array(
                array(
                    'js_engine' => 'node',
                    'build' => array('optimize' => 'test'),
                ),
                '\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException'
            ),
        );
    }

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
                array(
                    'js_engine' => 'node'
                ),
                array(
                    'js_engine' => 'node',
                    'config' => array(
                        'waitSeconds' => 0,
                    ),
                    'web_root' => '%kernel.root_dir%/../web',
                    'build_path' => 'js/app.min.js',
                    'building_timeout' => 60,
                    'build' => array(
                        'optimize' => 'uglify2',
                        'paths' => array(),
                    )
                )
            ),
            array(
                array(
                    'config' => array(
                        'waitSeconds' => 0,
                        'enforceDefine' => true,
                        'scriptType' => 'text/javascript'
                    ),
                    'js_engine' => 'node',
                    'build_path' => 'js/test/app.min.js',
                    'building_timeout' => 3600,
                    'build' => array(
                        'optimize' => 'none',
                        'generateSourceMaps' => false,
                        'preserveLicenseComments' => true,
                        'useSourceUrl' => false,
                        'paths' => array(),
                    )
                ),
                array(
                    'config' => array(
                        'waitSeconds' => 0,
                        'enforceDefine' => true,
                        'scriptType' => 'text/javascript',
                    ),
                    'js_engine' => 'node',
                    'web_root' => '%kernel.root_dir%/../web',
                    'build_path' => 'js/test/app.min.js',
                    'building_timeout' => 3600,
                    'build' => array(
                        'optimize' => 'none',
                        'generateSourceMaps' => false,
                        'preserveLicenseComments' => 1,
                        'useSourceUrl' => false,
                        'paths' => array(),
                    ),
                )
            ),
        );
    }
}
