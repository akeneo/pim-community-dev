<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Cache;

use Oro\Bundle\EntityConfigBundle\Cache\FileCache;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

class FileCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileCache
     */
    private $fileCache;

    private $testConfig;

    private $testConfigId;

    private $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir() . '/__phpunit__config_file_cache';
        mkdir($this->cacheDir);

        $this->testConfigId = new EntityConfigId('Test/Class', 'testScope');
        $this->testConfig   = new Config($this->testConfigId);
        $this->fileCache    = new FileCache($this->cacheDir);
    }

    protected function tearDown()
    {
        rmdir($this->cacheDir);
    }

    public function testCache()
    {
        $result = $this->fileCache->loadConfigFromCache($this->testConfigId);
        $this->assertEquals(null, $result);

        $this->fileCache->putConfigInCache($this->testConfig);

        $result = $this->fileCache->loadConfigFromCache($this->testConfigId);
        $this->assertEquals($this->testConfig, $result);

        $this->fileCache->removeConfigFromCache($this->testConfigId);
        $result = $this->fileCache->loadConfigFromCache($this->testConfigId);
        $this->assertEquals(null, $result);
    }

    public function testExceptionNotFoundDirectory()
    {
        $cacheDir = '/__phpunit__config_file_cache_wrong';
        $this->setExpectedException('\InvalidArgumentException', sprintf('The directory "%s" does not exist.', $cacheDir));
        $this->fileCache = new FileCache($cacheDir);
    }

    public function testExceptionNotWritableDirectory()
    {
        $cacheDir = '/';
        $this->setExpectedException('\InvalidArgumentException', sprintf('The directory "%s" is not writable.', $cacheDir));
        $this->fileCache = new FileCache($cacheDir);
    }
}
