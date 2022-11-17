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
