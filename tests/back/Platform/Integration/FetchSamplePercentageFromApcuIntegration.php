<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration;

use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\FetchSamplePercentage;
use Akeneo\Platform\Bundle\FrameworkBundle\AclCache\FetchSamplePercentageFromApcu;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class FetchSamplePercentageFromApcuIntegration extends TestCase
{
    public function test_it_gets_sample_values_from_apcu()
    {
        $fetchSamplePercentage = new class implements FetchSamplePercentage {
            private int $value = 10;
            public function fetch(): int
            {
                return $this->value++;
            }
        };

        $fetchSamplePercentage = new FetchSamplePercentageFromApcu($fetchSamplePercentage);
        $value = $fetchSamplePercentage->fetch();
        Assert::assertEquals(10, $value);
    }

    public function test_it_gets_does_not_call_decorated_fetcher_after_first_call()
    {
        $fetchSamplePercentage = new class implements FetchSamplePercentage {
            private int $value = 10;
            public function fetch(): int
            {
                return $this->value++;
            }
        };

        $fetchSamplePercentage = new FetchSamplePercentageFromApcu($fetchSamplePercentage);
        $fetchSamplePercentage->fetch();
        $fetchSamplePercentage->fetch();
        $value = $fetchSamplePercentage->fetch();

        Assert::assertEquals(10, $value);
    }
}
