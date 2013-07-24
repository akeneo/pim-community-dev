<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AsseticBundle\DependencyInjection\OroAsseticExtension;

class OroAsseticExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAssets()
    {
        $extension = new OroAsseticExtension();

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->once())
            ->method('getParameter')
            ->will(
                $this->returnValue(
                    array(
                        'Oro\Bundle\AsseticBundle\Tests\Unit\Fixtures\TestBundle'
                    )
                )
            );

        $assets = $extension->getAssets(
            $container,
            array('css_debug' => array(), 'js_debug' => array(), 'css_debug_all' => false, 'js_debug_all' => true)
        );

        $this->assertEquals('second.css', $assets['css']['compress'][0][1]);
        $this->assertEquals('first.js', $assets['js']['uncompress'][0][0]);
    }
}
