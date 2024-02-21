<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RanksDistributionSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_it_contains_an_invalid_rank()
    {
        $this->beConstructedWith([
            'rank_1' => 1,
            'rank_2' => 1,
            'rank_3' => 1,
            'rank_4' => 1,
            'rank_5' => 1,
            'rank_6' => 1,
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_percentage_per_rank()
    {
        $this->beConstructedWith([
            'rank_1' => 137,
            'rank_2' => 49,
            'rank_3' => 151,
            'rank_4' => 0,
            'rank_5' => 233,
        ]);

        $this->getPercentages()->shouldReturn([
            'rank_1' => 24.04,
            'rank_2' => 8.6,
            'rank_3' => 26.49,
            'rank_4' => 0.0,
            'rank_5' => 40.88,
        ]);
    }

    public function it_returns_the_average_rank()
    {
        $this->beConstructedWith([
            'rank_1' => 137,
            'rank_2' => 49,
            'rank_3' => 151,
            'rank_4' => 0,
            'rank_5' => 133,
        ]);

        $this->getAverageRank()->shouldBeLike(Rank::fromInt(3));
    }

    public function it_returns_null_as_average_rank_if_all_the_ranks_are_empty()
    {
        $this->beConstructedWith([
            'rank_1' => 0,
            'rank_2' => 0,
            'rank_3' => 0,
            'rank_4' => 0,
            'rank_5' => 0,
        ]);

        $this->getAverageRank()->shouldReturn(null);
    }
}
