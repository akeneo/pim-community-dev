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

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection as DqiChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQuery as DqiGetLatestProductScoresByIdentifiersQuery;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ChannelLocaleRateCollection;

class GetLatestProductScoresByIdentifiersQuery implements GetLatestProductScoresByIdentifiersQueryInterface
{
    private DqiGetLatestProductScoresByIdentifiersQuery $dqiGetLatestProductScoresByIdentifiersQuery;

    public function __construct(DqiGetLatestProductScoresByIdentifiersQuery $dqiGetLatestProductScoresByIdentifiersQuery)
    {
        $this->dqiGetLatestProductScoresByIdentifiersQuery = $dqiGetLatestProductScoresByIdentifiersQuery;
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        $dqiChannelLocaleRateCollections = $this->dqiGetLatestProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);
        return array_map(
            static fn (DqiChannelLocaleRateCollection $dqiChannelLocaleRateCollection) => ChannelLocaleRateCollection::fromArrayInt($dqiChannelLocaleRateCollection->toArrayInt()),
            $dqiChannelLocaleRateCollections
        );
    }

    public function byProductIdentifier(string $identifier): ChannelLocaleRateCollection
    {
        $dqiChannelLocaleRateCollection = $this->dqiGetLatestProductScoresByIdentifiersQuery->byProductIdentifier($identifier);
        return ChannelLocaleRateCollection::fromArrayInt($dqiChannelLocaleRateCollection->toArrayInt());
    }
}
