<?php
namespace Oro\Bundle\AsseticBundle\Tests\Unit\Node;

use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;

use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;

class OroAsseticNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroAsseticNode
     */
    private $node;

    private $compressAsset;
    private $unCompressAsset;

    public function setUp()
    {
        $asset = new FileAsset('first.less.css');

        $this->compressAsset = new AssetCollection(array($asset));
        $this->unCompressAsset = new AssetCollection(array($asset));
        $this->node = new OroAsseticNode(
            array(
                'compress' => $this->compressAsset,
                'un_compress' => $this->unCompressAsset
            ),
            array(
                'un_compress' => 'uncompress_test_asset',
                'compress' => 'compress_test_asset'
            ),
            array(),
            array('compile1.css', 'compile2.css'),
            new \Twig_Node(),
            array(),
            10,
            'oro_css'
        );
    }

    public function testGetUnCompressAsset()
    {
        $this->assertEquals($this->unCompressAsset, $this->node->getUnCompressAsset());
    }

    public function testGetCompressAsset()
    {
        $this->assertEquals($this->compressAsset, $this->node->getCompressAsset());
    }

    public function testGetNameUnCompress()
    {
        $this->assertEquals('uncompress_test_asset', $this->node->getNameUnCompress());
    }

    public function testCompile()
    {
        $compiler = $this->assetsFactory = $this->getMockBuilder('\Twig_Compiler')
            ->disableOriginalConstructor()
            ->getMock();

        $compiler->expects($this->any())
            ->method('write')
            ->will($this->returnValue($compiler));

        $compiler->expects($this->any())
            ->method('repr')
            ->will($this->returnValue($compiler));

        $compiler->expects($this->any())
            ->method('raw')
            ->will($this->returnValue($compiler));

        $this->node->compile($compiler);
    }
}
