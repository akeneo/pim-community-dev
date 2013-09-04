<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\Routing;

use Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource;
use Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Oro\Bundle\AsseticBundle\Routing\AsseticLoader;
use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;

class AsseticLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AsseticLoader
     */
    private $loader;

    /**
     * @var OroAssetManager
     */
    private $am;

    public function setUp()
    {
        $this->am = $this->getMockBuilder('Oro\Bundle\AsseticBundle\Factory\OroAssetManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->loader = new AsseticLoader($this->am);
    }

    public function testLoad()
    {
        $this->am->am = $this->getMockBuilder('Assetic\Factory\LazyAssetManager')
            ->disableOriginalConstructor()
            ->getMock();

        $loader = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader')
            ->disableOriginalConstructor()
            ->getMock();

        $dir = new DirectoryResource(
            $loader,
            'OroTestBundle',
            'app/Resources/OroTestBundle/views/',
            '"/\.[^.]+\.twig$/"'
        );

        $resource = new CoalescingDirectoryResource(array($dir, $dir));

        $this->am->am->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue(array($resource)));

        $assetNode = $this->getMockBuilder('Oro\Bundle\AsseticBundle\Node\OroAsseticNode')
            ->disableOriginalConstructor()
            ->getMock();

        $asset = new FileAsset('first.less.css');

        $assetCollection = new AssetCollection(array($asset));

        $assetNode->expects($this->any())
            ->method('getUnCompressAsset')
            ->will($this->returnValue($assetCollection));

        $this->am->expects($this->any())
            ->method('getAssets')
            ->will($this->returnValue(array('841e611' => $assetNode)));

        /** @var $routeCollection \Symfony\Component\Routing\RouteCollection */
        $routeCollection = $this->loader->load('', 'oro_assetic');
        /** @var $route \Symfony\Component\Routing\Route */
        $route = $routeCollection->get('_assetic_841e611_0');
        $this->assertEquals('/_first.less_1', $route->getPattern());
    }

    public function testSupports()
    {
        $resource = '';
        $this->assertTrue($this->loader->supports($resource, 'oro_assetic'));
        $this->assertFalse($this->loader->supports($resource, 'assetic'));
    }
}
