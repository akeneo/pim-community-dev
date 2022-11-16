<?php

namespace Akeneo\Platform\Bundle\FrameworkBundle\AclCache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FlushableCache;
use Doctrine\Common\Cache\MultiOperationCache;

class FetchSamplePercentageFromApcu implements FetchSamplePercentage
{
    private const APCU_KEY = 'acl_sample_percentage';
    private const TTL_IN_SECONDS = 300;

    public function __construct(
        private readonly FetchSamplePercentage $fetchSamplePercentage
    ) {
    }

    public function fetch(): int
    {
        $samplePercentage = \apcu_fetch(self::APCU_KEY);
        if (!is_int($samplePercentage)) {
            $samplePercentageFromBucket = $this->fetchSamplePercentage->fetch();
            \apcu_store(self::APCU_KEY, $samplePercentageFromBucket, self::TTL_IN_SECONDS);

            return $samplePercentageFromBucket;
        }

        return $samplePercentage;
    }
}
