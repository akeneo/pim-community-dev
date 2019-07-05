<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics;
use PhpSpec\ObjectBehavior;

class CreditsUsageStatisticsSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            [
                'consumed' => 2,
                'left' => 1,
                'total' => 3,
            ]
        );
    }

    public function it_is_a_credits_usage_statistics(): void
    {
        $this->shouldHaveType(CreditsUsageStatistics::class);
    }

    public function it_gets_credits_consumed(): void
    {
        $this->getConsumed()->shouldReturn(2);
    }

    public function it_gets_credits_left(): void
    {
        $this->getLeft()->shouldReturn(1);
    }

    public function it_gets_credits_total(): void
    {
        $this->getTotal()->shouldReturn(3);
    }

    public function it_throws_an_exception_if_some_fields_are_missing(): void
    {
        $this->beConstructedWith([]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
