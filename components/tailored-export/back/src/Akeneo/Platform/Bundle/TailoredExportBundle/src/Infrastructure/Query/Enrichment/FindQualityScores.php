<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindQualityScoresInterface;

class FindQualityScores implements FindQualityScoresInterface
{
    private GetLatestProductScoresQueryInterface $getLatestProductScoresQuery;

    public function __construct(GetLatestProductScoresQueryInterface $getLatestProductScoresQuery)
    {
        $this->getLatestProductScoresQuery = $getLatestProductScoresQuery;
    }

    public function forProduct(string $productIdentifier, string $channel, string $locale): ?string
    {
        $productScoreCollection = $this->getLatestProductScoresQuery->byProductIdentifier($productIdentifier);
        $productScore = $productScoreCollection->getProductScoreByChannelAndLocale($channel, $locale);

        return null !== $productScore ? $productScore->getLetter() : null;
    }
}
