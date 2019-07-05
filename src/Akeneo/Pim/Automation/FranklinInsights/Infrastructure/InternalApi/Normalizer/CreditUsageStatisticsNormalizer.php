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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\CreditsUsageStatistics;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreditUsageStatisticsNormalizer
{
    public function normalize(CreditsUsageStatistics $creditsUsageStatistics): array
    {
        $data = [];
        $data['credits_consumed'] = ['type' => 'number', 'value' => $creditsUsageStatistics->getConsumed()];
        $data['credits_left'] = ['type' => 'number', 'value' => $creditsUsageStatistics->getLeft()];
        $data['credits_total'] = ['type' => 'number', 'value' => $creditsUsageStatistics->getTotal()];

        return $data;
    }
}
