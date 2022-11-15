<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration;

use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\FetchSamplePercentage;
use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\SampleRouterCache;
use Doctrine\Common\Cache\ArrayCache;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class SampleRouterCacheIntegration extends TestCase
{
    public function test_it_fetches_from_source_of_truth_cache_when_sample_deactivated()
    {
        $fetchSamplePercentage = new class implements FetchSamplePercentage {
            public function fetch(): int
            {
                return 0;
            }
        };

        $sampledCache = new ArrayCache();
        $sourceOfTruthCache = new ArrayCache();

        $sampledCache->save('test', 'baz');
        $sourceOfTruthCache->save('test', 'foo');
        $sampleRouterCache = new SampleRouterCache($sampledCache, $sourceOfTruthCache, $fetchSamplePercentage);
        Assert::assertSame('foo', $sampleRouterCache->fetch('test'));
    }

    public function test_it_fetches_from_sample_cache_when_source_of_truth_deactivated()
    {
        $fetchSamplePercentage = new class implements FetchSamplePercentage {
            public function fetch(): int
            {
                return 100;
            }
        };

        $sampledCache = new ArrayCache();
        $sourceOfTruthCache = new ArrayCache();

        $sampledCache->save('test', 'baz');
        $sourceOfTruthCache->save('test', 'foo');
        $sampleRouterCache = new SampleRouterCache($sampledCache, $sourceOfTruthCache, $fetchSamplePercentage);
        Assert::assertSame('baz', $sampleRouterCache->fetch('test'));
    }

    public function test_it_guarantees_consistency_by_saving_in_both_cache()
    {
        $fetchSamplePercentage = new class implements FetchSamplePercentage {
            public function fetch(): int
            {
                return 100;
            }
        };

        $sampledCache = new ArrayCache();
        $sourceOfTruthCache = new ArrayCache();

        $sampleRouterCache = new SampleRouterCache($sampledCache, $sourceOfTruthCache, $fetchSamplePercentage);
        $sampleRouterCache->save('test', 'foo');

        Assert::assertSame('foo', $sourceOfTruthCache->fetch('test'));
        Assert::assertSame('foo', $sampledCache->fetch('test'));
    }
}
