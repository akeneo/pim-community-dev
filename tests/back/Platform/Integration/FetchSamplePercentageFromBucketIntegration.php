<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration;

use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\FetchSamplePercentageFromBucket;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class FetchSamplePercentageFromBucketIntegration extends TestCase
{
    public function test_it_gets_sample_values_from_bucket()
    {
        $fetchSamplePercentage = new FetchSamplePercentageFromBucket("akeneo-test");
        $value = $fetchSamplePercentage->fetch();

        Assert::assertEquals(66, $value);
    }
}
