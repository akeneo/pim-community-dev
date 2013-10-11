<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigCache;

use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

class ConfigCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        $configId      = new EntityConfigId('testClass', 'testScope');
        $cacheProvider = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $modelCacheProvider = $this->getMockBuilder('Doctrine\Common\Cache\CacheProvider')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $configCache = new ConfigCache($cacheProvider, $modelCacheProvider);

        $cacheProvider->expects($this->any())->method('fetch')->will($this->returnValue(false));
        $configCache->loadConfigFromCache($configId);
        //$this->assertFalse();
    }
}
