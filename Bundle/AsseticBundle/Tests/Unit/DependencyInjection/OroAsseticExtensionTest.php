<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AsseticBundle\DependencyInjection\OroAsseticExtension;

class OroAsseticExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testGetAssets
     *
     * @return array
     */
    public function getAssetsDataProvider()
    {
        return array(
            array(
                array('css_debug' => array(), 'css_debug_all' => true),
                array('compress' => array(array()), 'uncompress' => array(array('first.css', 'second.css'))),
            ),
            array(
                array('css_debug' => array(), 'css_debug_all' => false),
                array('compress' => array(array('first.css', 'second.css')), 'uncompress' => array(array())),
            ),
        );
    }

    /**
     * @dataProvider getAssetsDataProvider
     */
    public function testGetAssets($config, $expectedCss)
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

        $assets = $extension->getAssets($container, $config);
        $this->assertEquals($expectedCss, $assets['css']);
    }
}
