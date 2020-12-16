<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RanksDistributionCollectionSpec extends ObjectBehavior
{
    public function it_throws_an_exception_if_the_ranks_per_locale_are_malformed()
    {
        $this->beConstructedWith([
            "mobile" => null,
            "ecommerce" => [
                "en_US" => [
                  "rank_1" => 33,
                ]
            ],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_the_ranks_are_malformed()
    {
        $this->beConstructedWith([
            "mobile" => [
                "en_US" => null,
            ],
            "ecommerce" => [
                "en_US" => [
                    "rank_1" => 33,
                ],
            ],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_average_ranks()
    {
        $this->beConstructedWith([
            "mobile" => [
                "en_US" => [
                    'rank_1' => 10,
                    'rank_2' => 42,
                    'rank_3' => 5,
                ],
                "fr_FR" => [
                    "rank_3" => 33,
                ]
            ],
        ]);

        $this->getAverageRanks()->shouldBeLike([
            "mobile" => [
                "en_US" => Rank::fromString('rank_2'),
                "fr_FR" => Rank::fromString('rank_3'),
            ],
        ]);
    }
}
