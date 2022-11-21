<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\MultiOperationCache;

/**
 * Route reading requests according either on the first provider or the second provider.
 * The choice is done according to a probability and is constant across the life of the process.
 *
 * This provider is used to switch from one cache to another one progressively.
 * Write are done on both providers to guarantee consistency.
 */
class SampleRouterCache implements Cache, FlushableCache, ClearableCache, MultiOperationCache
{
    public CacheProvider|null $providerToTarget = null;

    public function __construct(
        private readonly CacheProvider $sampledProvider,
        private readonly CacheProvider $sourceOfTruthProvider,
        private readonly FetchSamplePercentage $fetchSamplePercentage
    ) {
    }

    public function fetch($id)
    {
        return $this->getProviderToTarget()->fetch($id);
    }

    public function contains($id)
    {
        return $this->getProviderToTarget()->contains($id);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        $this->sampledProvider->save($id, $data, $lifeTime);

        return $this->sourceOfTruthProvider->save($id, $data, $lifeTime);
    }

    public function delete($id)
    {
        $this->sampledProvider->delete($id);

        return $this->sourceOfTruthProvider->delete($id);
    }

    public function getStats()
    {
        $this->sampledProvider->getStats();

        return $this->sourceOfTruthProvider->getStats();
    }

    public function deleteAll()
    {
        $this->sampledProvider->deleteAll();

        return $this->sourceOfTruthProvider->deleteAll();
    }

    public function flushAll()
    {
        $this->sampledProvider->flushAll();

        return $this->sourceOfTruthProvider->flushAll();
    }

    public function deleteMultiple(array $keys)
    {
        $this->sampledProvider->deleteMultiple();

        return $this->sourceOfTruthProvider->deleteMultiple($keys);
    }

    public function fetchMultiple(array $keys)
    {
        return $this->getProviderToTarget()->fetchMultiple($keys);
    }

    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $this->sampledProvider->saveMultiple($keysAndValues, $lifetime);

        return $this->sourceOfTruthProvider->saveMultiple($keysAndValues, $lifetime);
    }

    private function getProviderToTarget(): CacheProvider
    {
        if ($this->providerToTarget === null) {
            $this->providerToTarget = mt_rand(1, 100) <=  $this->fetchSamplePercentage->fetch() ? $this->sampledProvider : $this->sourceOfTruthProvider;
        }

        return $this->providerToTarget;
    }
}
