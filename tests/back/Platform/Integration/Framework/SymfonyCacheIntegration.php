<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Framework;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SymfonyCacheIntegration extends TestCase
{
    const CACHE_KEY = 'test_cache';
    const CACHE_VALUE = 'f7d4761d-33f7-4c30-adcb-924e4aaaa985';

    private AdapterInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->get('cache.data');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cache->clear();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_cache_is_working(): void
    {
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        Assert::assertFalse($cacheItem->isHit());
        Assert::assertNull($cacheItem->get());

        $cacheItem->set(self::CACHE_VALUE);
        $this->cache->save($cacheItem);

        $cacheItem = $this->cache->getItem(self::CACHE_KEY);
        Assert::assertTrue($cacheItem->isHit());
        Assert::assertEquals(self::CACHE_VALUE, $cacheItem->get());
    }
}
