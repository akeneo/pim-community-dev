<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\Factory;

use Assetic\Asset\FileAsset;
use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;
use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

class OroAssetManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroAssetManager
     */
    private $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $am;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $twig;

    public function setUp(): void
    {
        $this->am = $this->getMockBuilder('Assetic\Factory\LazyAssetManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new OroAssetManager($this->am, $this->twig, ['assetGroups'], ['compiledGroup']);
    }

    public function testGetGroups()
    {
        $data = $this->manager->getAssetGroups();
        $this->assertEquals('assetGroups', $data[0]);
    }

    public function testCompiledGroups()
    {
        $data = $this->manager->getCompiledGroups();
        $this->assertEquals('compiledGroup', $data[0]);
    }

    public function testGetAssets()
    {
        $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();

        $barAsset = $this->createMockOroAsseticNode('uncompress_bar_asset');
        $fooAsset = $this->createMockOroAsseticNode('uncompress_foo_asset', [$barAsset]);

        $this->am->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue([$resource]));

        $this->twig->expects($this->once())
            ->method('tokenize')
            ->with('resource_content', 'resource_name')
            ->will($this->returnValue($token));

        $this->twig->expects($this->once())
            ->method('parse')
            ->with($token)
            ->will($this->returnValue($fooAsset));

        $this->assertEquals(
            [
                'uncompress_foo_asset' => $fooAsset,
                'uncompress_bar_asset' => $barAsset,
            ],
            $this->manager->getAssets()
        );
    }

    public function testGet()
    {
        $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();
        $asset = $this->createMockOroAsseticNode('uncompress_test_asset');

        $assetFile = new FileAsset('test.css');
        $asset->expects($this->once())->method('getUnCompressAsset')->will($this->returnValue($assetFile));

        $this->am->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue([$resource]));

        $this->twig->expects($this->once())
            ->method('tokenize')
            ->with('resource_content', 'resource_name')
            ->will($this->returnValue($token));

        $this->twig->expects($this->once())
            ->method('parse')
            ->with($token)
            ->will($this->returnValue($asset));

        $this->assertEquals(
            $assetFile,
            $this->manager->get('uncompress_test_asset')
        );
    }

    public function testHas()
    {
        $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();
        $asset = $this->createMockOroAsseticNode('uncompress_test_asset');

        $this->am->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue([$resource]));

        $this->twig->expects($this->once())
            ->method('tokenize')
            ->with('resource_content', 'resource_name')
            ->will($this->returnValue($token));

        $this->twig->expects($this->once())
            ->method('parse')
            ->with($token)
            ->will($this->returnValue($asset));

        $this->assertTrue($this->manager->has('uncompress_test_asset'));
    }

    protected function createMockResource($name, $content)
    {
        $result = $this->createMock('Assetic\Factory\Resource\ResourceInterface');

        $result->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($content));

        $result->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($name));

        return $result;
    }

    protected function createMockOroAsseticNode($nameUnCompress, array $children = [])
    {
        $result = $this->getMockBuilder(OroAsseticNode::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNameUnCompress', 'getUnCompressAsset', 'getAttribute', 'getIterator'])
            ->getMock();

        $result->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($children)));

        $result->expects($this->once())
            ->method('getNameUnCompress')
            ->will($this->returnValue($nameUnCompress));

        return $result;
    }
}
