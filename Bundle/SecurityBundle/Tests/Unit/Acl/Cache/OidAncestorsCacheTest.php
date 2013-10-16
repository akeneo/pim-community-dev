<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Cache;

use Oro\Bundle\SecurityBundle\Acl\Cache\OidAncestorsCache;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class OidAncestorsCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OidAncestorsCache
     */
    protected $oidAncestorCache;

    /**
     * @var ObjectIdentity
     */
    protected $objectIdentity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheProvider;

    /**
     * @var string
     */
    protected $oidKey;

    public function setUp()
    {
        $this->objectIdentity = new ObjectIdentity('testId', 'testType');
        $this->cacheProvider = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->setMethods(array('fetch', 'save', 'delete', 'deleteAll'))
            ->getMockForAbstractClass();

        $this->oidAncestorCache = new OidAncestorsCache($this->cacheProvider);
        $this->oidKey = 'oid_ancestor_testIdtestType';
    }

    public function testGetAncestorsFromCache()
    {
        $testData = array('test');
        $this->cacheProvider->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($this->oidKey))
            ->will($this->returnValue(serialize($testData)));

        $result = $this->oidAncestorCache->getAncestorsFromCache($this->objectIdentity);

        $this->assertEquals($testData[0], $result[0]);
    }

    public function testPutAncestorsInCache()
    {
        $this->cacheProvider->expects($this->once())
            ->method('save')
            ->with($this->equalTo($this->oidKey))
            ->will($this->returnValue(true));

        $this->assertTrue($this->oidAncestorCache->putAncestorsInCache($this->objectIdentity, array()));
    }

    public function testRemoveAncestorsFromCache()
    {
        $this->cacheProvider->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($this->oidKey))
            ->will($this->returnValue(true));

        $this->assertTrue($this->oidAncestorCache->removeAncestorsFromCache($this->objectIdentity));
    }

    public function testRemoveAll()
    {
        $this->cacheProvider->expects($this->once())
            ->method('deleteAll')
            ->will($this->returnValue(true));

        $this->assertTrue($this->oidAncestorCache->removeAll());
    }
}
