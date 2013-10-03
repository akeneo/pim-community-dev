<?php
namespace Oro\Bundle\RequireJSBundle\Tests\Unit\Twig;

use Oro\Bundle\RequireJSBundle\Twig\OroRequireJSExtension;

class OroRequireJSExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $functions = array(
        'get_requirejs_config_path' => array('oro_require_js.config_path', 'require-config.js'),
        'get_requirejs_build_path' => array('oro_require_js.build_path', 'oro.min.js'),
    );

    public function testGetFunctions()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValueMap(array_values($this->functions)));

        $extension = new OroRequireJSExtension($container);

        /** @var \Twig_SimpleFunction[] $functions */
        $functions = $extension->getFunctions();
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertArrayHasKey($function->getName(), $this->functions);
            $this->assertEquals(
                $this->functions[$function->getName()][1],
                call_user_func($function->getCallable())
            );
        }
    }


    public function testGetName()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $extension = new OroRequireJSExtension($container);
        $this->assertEquals('requirejs_extension', $extension->getName());
    }
}
