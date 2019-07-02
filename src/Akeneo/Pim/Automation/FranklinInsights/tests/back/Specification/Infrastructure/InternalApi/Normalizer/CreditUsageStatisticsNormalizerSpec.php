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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\CreditsUsageStatistics;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer\CreditUsageStatisticsNormalizer;
use PhpSpec\ObjectBehavior;

class CreditUsageStatisticsNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_credit_usage_statistics_normalizer(): void
    {
        $this->shouldHaveType(CreditUsageStatisticsNormalizer::class);
    }

    public function it_normalizes_a_credit_usage_statistics(): void
    {
        $creditUsageStatistics = new CreditsUsageStatistics(3, 5, 1);

        $this->normalize($creditUsageStatistics)->shouldReturn(
            [
                'credits_consumed' => [
                    'type' => KeyFigure::TYPE_NUMBER,
                    'value' => 3,
                ],
                'credits_left' => [
                    'type' => KeyFigure::TYPE_NUMBER,
                    'value' => 5,
                ],
                'credits_total' => [
                    'type' => KeyFigure::TYPE_NUMBER,
                    'value' => 1,
                ]
            ]
        );
    }
}
