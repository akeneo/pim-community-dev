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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $am;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $twig;

    public function setUp()
    {
        $this->am = $this->getMockBuilder('Assetic\Factory\LazyAssetManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->twig = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new OroAssetManager($this->am, $this->twig, array('assetGroups'), array('compiledGroup'));
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
        $fooAsset = $this->createMockOroAsseticNode('uncompress_foo_asset', array($barAsset));

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array(
                    array('getResources', array(), $this->returnValue(array($resource)))
                )
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array(
                    array('tokenize', array('resource_content', 'resource_name'), $this->returnValue($token)),
                    array('parse', array($token), $this->returnValue($fooAsset))
                )
            )
        );

        $this->assertEquals(
            array(
                'uncompress_foo_asset' => $fooAsset,
                'uncompress_bar_asset' => $barAsset,
            ),
            $this->manager->getAssets()
        );
    }

    public function testSaveAssetsToCache()
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->setMethods(array('fetch', 'save'))
            ->getMockForAbstractClass();
        $this->manager->setCache($cache);

                $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();
        $asset = $this->createMockOroAsseticNode('uncompress_test_asset');

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array(
                    array('getResources', array(), $this->returnValue(array($resource)))
                )
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array(
                    array('tokenize', array('resource_content', 'resource_name'), $this->returnValue($token)),
                    array('parse', array($token), $this->returnValue($asset))
                )
            ),
            array(
                'mock' => $cache,
                'expectedCalls' => array(
                    array('fetch', array('assets'), $this->returnValue(false)),
                    array(
                        'save',
                        array('assets', $this->stringStartsWith('a:1:{s:21:"uncompress_test_asset"')),
                        $this->returnValue(false)
                    ),
                )
            )
        );

        $this->assertEquals(
            array('uncompress_test_asset' => $asset),
            $this->manager->getAssets()
        );
    }

    public function testFetchAssetsFromCache()
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->setMethods(array('fetch', 'save'))
            ->getMockForAbstractClass();
        $this->manager->setCache($cache);

        $cachedAssets = array(
            'foo' => new \stdClass()
        );

        $this->addMockExpectedCalls(
            array(
                'mock' => $cache,
                'expectedCalls' => array(
                    array('fetch', array('assets'), $this->returnValue(serialize($cachedAssets))),
                )
            )
        );

        $this->assertEquals($cachedAssets, $this->manager->getAssets());
    }

    public function testGet()
    {
        $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();
        $asset = $this->createMockOroAsseticNode('uncompress_test_asset');

        $assetFile = new FileAsset('test.css');
        $asset->expects($this->once())->method('getUnCompressAsset')->will($this->returnValue($assetFile));

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array(
                    array('getResources', array(), $this->returnValue(array($resource)))
                )
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array(
                    array('tokenize', array('resource_content', 'resource_name'), $this->returnValue($token)),
                    array('parse', array($token), $this->returnValue($asset))
                )
            )
        );

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

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array(
                    array('getResources', array(), $this->returnValue(array($resource)))
                )
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array(
                    array('tokenize', array('resource_content', 'resource_name'), $this->returnValue($token)),
                    array('parse', array($token), $this->returnValue($asset))
                )
            )
        );

        $this->assertTrue($this->manager->has('uncompress_test_asset'));
    }

    public function testHasFormula()
    {
        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array()
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array()
            )
        );

        $this->assertTrue($this->manager->hasFormula('uncompress_test_asset'));
    }

    public function testGetFormula()
    {
        $resource = $this->createMockResource('resource_name', 'resource_content');
        $token = $this->getMockBuilder('Twig_TokenStream')->disableOriginalConstructor()->getMock();
        $asset = $this->createMockOroAsseticNode('uncompress_test_asset');

        $inputsAttribute = array('foo');
        $asset->expects($this->once())->method('getAttribute')
            ->with('inputs')->will($this->returnValue($inputsAttribute));

        $this->addMockExpectedCalls(
            array(
                'mock' => $this->am,
                'expectedCalls' => array(
                    array('getResources', array(), $this->returnValue(array($resource)))
                )
            ),
            array(
                'mock' => $this->twig,
                'expectedCalls' => array(
                    array('tokenize', array('resource_content', 'resource_name'), $this->returnValue($token)),
                    array('parse', array($token), $this->returnValue($asset))
                )
            )
        );

        $this->assertEquals(array($inputsAttribute), $this->manager->getFormula('uncompress_test_asset'));
    }

    public function testGetLastModified()
    {
        $asset = $this->getMock('Assetic\Asset\AssetInterface');

        $this->am->expects($this->any())
            ->method('getLastModified')
            ->will($this->returnValue(123));

        $this->assertEquals(123, $this->manager->getLastModified($asset));
    }

    protected function createMockResource($name, $content)
    {
        $result = $this->getMock('Assetic\Factory\Resource\ResourceInterface');

        $result->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($content));

        $result->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($name));

        return $result;
    }

    protected function createMockOroAsseticNode($nameUnCompress, array $children = array())
    {
        $result = $this->getMockBuilder('Oro\Bundle\AsseticBundle\Node\OroAsseticNode')
            ->disableOriginalConstructor()
            ->setMethods(array('getNameUnCompress', 'getUnCompressAsset', 'getAttribute', 'getIterator'))
            ->getMock();

        $result->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($children)));

        $result->expects($this->once())
            ->method('getNameUnCompress')
            ->will($this->returnValue($nameUnCompress));

        return $result;
    }

    protected function addMockExpectedCalls()
    {
        $mocksExpectedCalls = func_get_args();
        foreach ($mocksExpectedCalls as $mockExpectedCalls) {
            /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
            list($mock, $expectedCalls) = array_values($mockExpectedCalls);
            if ($expectedCalls) {
                $index = 0;
                foreach ($expectedCalls as $expectedCall) {
                    $expectedCall = array_pad($expectedCall, 3, null);
                    list($method, $arguments, $result) = $expectedCall;
                    $methodExpectation = $mock->expects(\PHPUnit_Framework_TestCase::at($index++))->method($method);
                    $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                    if ($expectedCall) {
                        $methodExpectation->will($result);
                    }
                }
            } else {
                $mock->expects(\PHPUnit_Framework_TestCase::never())->method(\PHPUnit_Framework_TestCase::anything());
            }
        };
    }
}
