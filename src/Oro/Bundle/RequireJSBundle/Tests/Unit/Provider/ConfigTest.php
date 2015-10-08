<?php

namespace Oro\Bundle\RequireJSBundle\Tests\Unit\Provider;

use Oro\Bundle\RequireJSBundle\Provider\Config as RequireJSConfigProvider;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequireJSConfigProvider
     */
    protected $configProvider;

    public function setUp()
    {
        $parameters = array(
            'oro_require_js' => array(
                'build_path' => 'js/app.min.js'
            ),
            'kernel.bundles' => array('Oro\Bundle\RequireJSBundle\Tests\Unit\Fixtures\TestBundle')
        );

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(
                function ($name) use (&$parameters) {
                    return $parameters[$name];
                }
            ));

        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->any())
            ->method('render')
            ->will($this->returnArgument(1));

        $template = '';

        $this->configProvider = new RequireJSConfigProvider($container, $templating, $template);
    }

    public function testGetMainConfig()
    {
        $expected = array(
            'config' => array(
                'paths' => array(
                    'oro/test' => 'orotest/js/test'
                )
            )
        );
        $this->assertEquals($expected, $this->configProvider->getMainConfig());

        $expected['config']['paths']['oro/test2'] = 'orotest/js/test2';

        $cache = $this->getMock('\Doctrine\Common\Cache\PhpFileCache', array(), array(), '', false);
        $cache->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue($expected));
        $this->configProvider->setCache($cache);

        $this->assertEquals($expected, $this->configProvider->getMainConfig());
    }

    public function testGenerateMainConfig()
    {
        $this->assertEquals(
            array(
                'config' => array(
                    'paths' => array(
                        'oro/test' => 'orotest/js/test'
                    )
                )
            ),
            $this->configProvider->generateMainConfig()
        );
    }

    public function testGenerateBuildConfig()
    {
        $this->assertEquals(
            array(
                'paths' => array(
                    'oro/test' => 'empty:',
                    'require-config' => '../main-config',
                    'require-lib' => 'ororequirejs/lib/require',
                ),
                'baseUrl' => './bundles',
                'out' => './js/app.min.js',
                'mainConfigFile' => './main-config.js',
                'include' => array('require-config', 'require-lib', 'oro/test')
            ),
            $this->configProvider->generateBuildConfig('main-config.js')
        );
    }

    public function testCollectConfigs()
    {
        $this->assertEquals(
            array(
                'build_path' => 'js/app.min.js',
                'config' => array(
                    'paths' => array(
                        'oro/test' => 'bundles/orotest/js/test.js'
                    )
                ),
                'build' => array(
                    'paths' => array(
                        'oro/test' => 'empty:'
                    )
                )
            ),
            $this->configProvider->collectConfigs()
        );
    }
}
