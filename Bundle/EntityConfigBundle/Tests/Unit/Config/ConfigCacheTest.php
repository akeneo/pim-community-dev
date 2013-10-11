<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigCache;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        $cacheProvider = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('fetch', 'save', 'delete', 'deleteAll'))
            ->getMockForAbstractClass();

        $modelCacheProvider = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('fetch', 'save', 'delete', 'deleteAll'))
            ->getMockForAbstractClass();

        $className   = 'testClass';
        $scope       = 'testScope';
        $configId    = new EntityConfigId($className, $scope);
        $config      = new Config($configId);
        $configCache = new ConfigCache($cacheProvider, $modelCacheProvider);

        $cacheProvider->expects($this->once())->method('save')->will($this->returnValue(true));
        $this->assertTrue($configCache->putConfigInCache($config));

        $cacheProvider->expects($this->once())->method('delete')->will($this->returnValue(true));
        $this->assertTrue($configCache->removeConfigFromCache($configId));

        $cacheProvider->expects($this->once())->method('deleteAll')->will($this->returnValue(true));
        $this->assertTrue($configCache->removeAll());

        $cacheProvider->expects($this->once())->method('fetch')->will($this->returnValue(serialize($config)));
        $this->assertEquals($config, $configCache->loadConfigFromCache($configId));

        $value = 'testValue';

        $modelCacheProvider->expects($this->once())->method('save')->will($this->returnValue(true));
        $this->assertTrue($configCache->setConfigurable($value, $className));

        $modelCacheProvider->expects($this->once())->method('fetch')->will($this->returnValue($value));
        $this->assertEquals($value, $configCache->getConfigurable($className));

        $modelCacheProvider->expects($this->once())->method('deleteAll')->will($this->returnValue(true));
        $this->assertTrue($configCache->removeAllConfigurable());
    }
}
