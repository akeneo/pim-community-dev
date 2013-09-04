<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Entity;

use Oro\Bundle\SecurityBundle\Entity\SecurityMetadata;
use Oro\Bundle\SecurityBundle\Entity\SecurityMetadataProvider as Provider;

class SecurityMetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    /** @var SecurityMetadata */
    protected $entity;

    protected function setUp()
    {
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('fetch', 'save', 'delete', 'deleteAll')
        );

        $this->entity = new SecurityMetadata(Provider::ACL_SECURITY_TYPE, 'SomeClass', 'SomeGroup');
    }

    public function testGetEntityList()
    {
        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $configId = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $configId->expects($this->once())->method('getClassName')->will($this->returnValue('SomeClass'));
        $config->expects($this->at(0))->method('get')->with('type')
            ->will($this->returnValue(Provider::ACL_SECURITY_TYPE));
        $config->expects($this->any())->method('getId')
            ->will($this->returnValue($configId));
        $config->expects($this->at(2))->method('get')->with('group_name')
            ->will($this->returnValue('SomeGroup'));
        $configs = array($config);
        $this->configProvider->expects($this->any())->method('getConfigs')
            ->will($this->returnValue($configs));

        $this->cache->expects($this->at(0))
            ->method('fetch')
            ->with(Provider::ACL_SECURITY_TYPE)
            ->will($this->returnValue(false));
        $this->cache->expects($this->at(2))
            ->method('fetch')
            ->with(Provider::ACL_SECURITY_TYPE)
            ->will($this->returnValue(array($this->entity)));
        $this->cache->expects($this->once())
            ->method('save')
            ->with(Provider::ACL_SECURITY_TYPE, array($this->entity));

        $provider = new Provider($this->configProvider, $this->cache);
        //call without cache
        $result = $provider->getEntityList();
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf('Oro\Bundle\SecurityBundle\Entity\SecurityMetadata', $result);
        $this->assertEquals(serialize($result), serialize(array($this->entity)));
        //call with cache
        $result = $provider->getEntityList();
        $this->assertCount(1, $result);
        $this->assertContains($this->entity, $result);
    }

    public function testClearCache()
    {
        $this->cache->expects($this->once())
            ->method('delete')
            ->with('SomeType');

        $this->cache->expects($this->once())
            ->method('deleteAll');

        $provider = new Provider($this->configProvider, $this->cache);

        $provider->clearCache('SomeType');
        $provider->clearCache();
    }
}
