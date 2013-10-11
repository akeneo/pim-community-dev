<?php

namespace Oro\Bundle\RequireJSBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\RequireJSBundle\DependencyInjection\OroRequireJSExtension;

class OroRequireJSExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedParameters = array(
        'oro_require_js' => array(),
        'oro_require_js.web_root' => '/path/to/web_root',
        'oro_require_js.build_path' => 'js/app.min.js',
    );

    /**
     * @var array
     */
    protected $config = array(
        'oro_translation' => array(
            'js_engine' => 'node',
            'web_root' => '/path/to/web_root',
            'build_path' => 'js/app.min.js',
        )
    );

    public function testLoad()
    {
        $actualParameters  = array();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->any())
            ->method('setParameter')
            ->will(
                $this->returnCallback(
                    function ($name, $value) use (&$actualParameters) {
                        $actualParameters[$name] = $value;
                    }
                )
            );

        $extension = new OroRequireJSExtension();
        $extension->load($this->config, $container);

        foreach ($this->expectedParameters as $parameterName => $expected) {
            $this->assertArrayHasKey($parameterName, $actualParameters);
            if (is_scalar($expected)) {
                $this->assertEquals($expected, $actualParameters[$parameterName]);
            } else {
                $this->assertNotEmpty($actualParameters[$parameterName]);
            }
        }
    }
}
