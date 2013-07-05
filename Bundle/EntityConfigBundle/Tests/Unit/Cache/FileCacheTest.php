<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Cache;

use Oro\Bundle\EntityConfigBundle\Cache\FileCache;
use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileCache
     */
    private $fileCache;

    private $testConfig;

    private $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir() . '/__phpunit__config_file_cache';
        mkdir($this->cacheDir);

        $this->testConfig = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'test');
        $this->fileCache  = new FileCache($this->cacheDir);
    }

    protected function tearDown()
    {
        rmdir($this->cacheDir);
    }

    public function testCache()
    {
        $result = $this->fileCache->loadConfigFromCache(ConfigManagerTest::DEMO_ENTITY, 'test');
        $this->assertEquals(null, $result);

        $this->fileCache->putConfigInCache($this->testConfig);

        $result = $this->fileCache->loadConfigFromCache(ConfigManagerTest::DEMO_ENTITY, 'test');
        $this->assertEquals($this->testConfig, $result);

        $this->fileCache->removeConfigFromCache(ConfigManagerTest::DEMO_ENTITY, 'test');
        $result = $this->fileCache->loadConfigFromCache(ConfigManagerTest::DEMO_ENTITY, 'test');
        $this->assertEquals(null, $result);
    }

    public function testExceptionNotFoundDirectory()
    {
        $cacheDir = '/__phpunit__config_file_cache_wrong';
        $this->setExpectedException('\InvalidArgumentException', sprintf('The directory "%s" does not exist.', $cacheDir));
        $this->fileCache  = new FileCache($cacheDir);
    }

    public function testExceptionNotWritableDirectory()
    {
        $cacheDir = '/';
        $this->setExpectedException('\InvalidArgumentException', sprintf('The directory "%s" is not writable.', $cacheDir));
        $this->fileCache  = new FileCache($cacheDir);
    }
}
