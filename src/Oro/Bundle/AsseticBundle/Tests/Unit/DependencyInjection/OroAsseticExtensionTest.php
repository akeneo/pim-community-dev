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
        return [
            [
                ['css_debug' => [], 'css_debug_all' => true],
                ['compress'  => [[]], 'uncompress' => [['first.css', 'second.css']]],
            ],
            [
                ['css_debug' => [], 'css_debug_all' => false],
                ['compress'  => [['first.css', 'second.css']], 'uncompress' => [[]]],
            ],
        ];
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
                    [
                        'Oro\Bundle\AsseticBundle\Tests\Unit\Fixtures\TestBundle'
                    ]
                )
            );

        $assets = $extension->getAssets($container, $config);
        $this->assertEquals($expectedCss, $assets['css']);
    }
}
