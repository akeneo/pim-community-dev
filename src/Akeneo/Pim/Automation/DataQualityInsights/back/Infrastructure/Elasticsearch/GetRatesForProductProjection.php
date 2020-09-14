<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductAxesRanksQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

final class GetRatesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    /** @var GetProductIdsFromProductIdentifiersQueryInterface */
    private $getProductIdsFromProductIdentifiersQuery;

    /** @var GetLatestProductAxesRanksQueryInterface */
    private $getLatestProductAxesRanksQuery;

    public function __construct(
        GetLatestProductAxesRanksQueryInterface $getLatestProductAxesRanksQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
    ) {
        $this->getProductIdsFromProductIdentifiersQuery = $getProductIdsFromProductIdentifiersQuery;
        $this->getLatestProductAxesRanksQuery = $getLatestProductAxesRanksQuery;
    }

    /**
     * @inheritDoc
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productIds = $this->getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers);
        $productAxesRanks = $this->getLatestProductAxesRanksQuery->byProductIds($productIds);

        $additionalProperties = [];
        foreach ($productIds as $productIdentifier => $productId) {
            $productId = $productId->toInt();
            $additionalProperties[$productIdentifier] = [
                'rates' => isset($productAxesRanks[$productId]) ? $productAxesRanks[$productId]->toArrayInt() : []
            ];
        }

        return $additionalProperties;
    }
}
