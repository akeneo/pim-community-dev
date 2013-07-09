<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\Factory;

use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;

use Oro\Bundle\AsseticBundle\Factory\OroAssetManager;
use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

class OroAssetManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroAssetManager
     */
    private $manager;

    private $am;
    private $twig;

    private $resource;
    private $token;
    private $node;

    private $compressAsset;
    private $unCompressAsset;

    private $assetFile;

    public function setUp()
    {
        $this->am = $this->getMockBuilder('Assetic\Factory\LazyAssetManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource = $this->getMock('Assetic\Factory\Resource\ResourceInterface');

        $this->resource->expects($this->any())
            ->method('isFresh')
            ->will($this->returnValue(true));

        $this->am->expects($this->any())
            ->method('getResources')
            ->will($this->returnValue(array($this->resource)));

        $this->token = $this->getMockBuilder('\Twig_TokenStream')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetFile = new FileAsset('test.css');

        $this->compressAsset = new AssetCollection(array($this->assetFile));

        $this->unCompressAsset = new AssetCollection(array($this->assetFile));

        $this->node = new OroAsseticNode(
            array(
                'name' => 'compress_test_asset',
                'data' => $this->compressAsset
            ),
            array(
                'name' => 'uncompress_test_asset',
                'data' => $this->unCompressAsset
            ),
            array(),
            array('test.css'),
            new \Twig_Node(),
            array(),
            10,
            'oro_css'
        );

        $this->twig->expects($this->any())
            ->method('tokenize')
            ->will($this->returnValue($this->token));

        $this->twig->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($this->node));


        $this->manager = new OroAssetManager($this->am, $this->twig);
    }

    public function testGetAssets()
    {
        $assets = $this->manager->getAssets();
        $this->assertEquals($this->node, $assets['uncompress_test_asset']);
    }

    public function testGet()
    {
        $assets = $this->manager->get('uncompress_test_asset');
        foreach($assets as $asset) {
            $this->assertEquals($this->assetFile->getSourcePath(), $asset->getSourcePath());
        }
    }

    public function testHas()
    {
        $this->assertTrue($this->manager->has('uncompress_test_asset'));
    }

    public function testHasFormula()
    {
        $this->assertTrue($this->manager->hasFormula('uncompress_test_asset'));
    }

    public function testGetFormula()
    {
        $formula = $this->manager->getFormula('uncompress_test_asset');
        $this->assertEquals($formula[0][0], 'test.css');
    }

    public function testGetLastModified()
    {
        $asset = $this->getMock('Assetic\Asset\AssetInterface');
        $child = $this->getMock('Assetic\Asset\AssetInterface');
        $filter1 = $this->getMock('Assetic\Filter\FilterInterface');
        $filter2 = $this->getMock('Assetic\Filter\DependencyExtractorInterface');

        $asset->expects($this->any())
            ->method('getLastModified')
            ->will($this->returnValue(123));
        $asset->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array($filter1, $filter2)));
        $child->expects($this->any())
            ->method('getLastModified')
            ->will($this->returnValue(456));
        $child->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue(array()));

        $this->assertEquals(123, $this->manager->getLastModified($asset));
    }
}