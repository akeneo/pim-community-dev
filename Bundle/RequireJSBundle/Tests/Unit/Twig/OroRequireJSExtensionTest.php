<?php
namespace Oro\Bundle\RequireJSBundle\Tests\Unit\Twig;

use Oro\Bundle\RequireJSBundle\Twig\OroRequireJSExtension;

class OroRequireJSExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $functions = array(
        'get_requirejs_config' => '{"config": "test"}',
        'get_requirejs_build_path' => 'oro.min.js',
        'requirejs_build_exists' => array(),
    );

    protected $parameters = array(
        array('oro_require_js.build_path', 'oro.min.js')
    );

    public function testGetFunctions()
    {
        $configProvider = $this->getMock('Oro\Bundle\RequireJSBundle\Provider\Config', array(), array(), '', false);
        $configProvider->expects($this->any())
            ->method('getMainConfig')
            ->will($this->returnValue($this->functions['get_requirejs_config']));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap($this->parameters));
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue($configProvider));

        $extension = new OroRequireJSExtension($container);

        /** @var \Twig_SimpleFunction[] $functions */
        $functions = $extension->getFunctions();
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertArrayHasKey($function->getName(), $this->functions);
            if ($function->getName() === 'requirejs_build_exists') {
                $this->assertInternalType('boolean', call_user_func($function->getCallable()));
            } else {
                $this->assertEquals(
                    $this->functions[$function->getName()],
                    call_user_func($function->getCallable())
                );
            }
        }
    }


    public function testGetName()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $extension = new OroRequireJSExtension($container);
        $this->assertEquals('requirejs_extension', $extension->getName());
    }
}
