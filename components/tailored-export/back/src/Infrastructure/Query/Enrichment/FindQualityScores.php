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

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindQualityScoresInterface;
use Ramsey\Uuid\UuidInterface;

class FindQualityScores implements FindQualityScoresInterface
{
    public function __construct(
        private GetProductScoresQueryInterface $getProductScoresQuery,
    ) {
    }

    public function forProduct(UuidInterface $productUuid, string $channel, string $locale): ?string
    {
        $productScoreCollection = $this->getProductScoresQuery->byProductUuid($productUuid);
        $productScore = $productScoreCollection->getQualityScoreByChannelAndLocale($channel, $locale);

        return null !== $productScore ? $productScore->getLetter() : null;
    }
}
