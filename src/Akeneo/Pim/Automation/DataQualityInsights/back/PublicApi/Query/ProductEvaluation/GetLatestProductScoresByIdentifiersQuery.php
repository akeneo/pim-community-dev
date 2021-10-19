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

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\ChannelLocaleRateCollection;

class GetLatestProductScoresByIdentifiersQuery implements GetLatestProductScoresByIdentifiersQueryInterface
{
    private GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery;

    public function __construct(GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery)
    {
        $this->getLatestProductScoresByIdentifiersQuery = $getLatestProductScoresByIdentifiersQuery;
    }

    public function byProductIdentifiers(array $productIdentifiers): array
    {
        return $this->getLatestProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);
    }

    public function byProductIdentifier(string $identifier): ChannelLocaleRateCollection
    {
        return $this->getLatestProductScoresByIdentifiersQuery->byProductIdentifier($identifier);
    }
}
