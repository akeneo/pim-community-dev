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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestProductAxesRatesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

final class GetRatesForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
{
    /** @var GetProductIdsFromProductIdentifiersQueryInterface */
    private $getProductIdsFromProductIdentifiersQuery;

    /** @var GetLatestProductAxesRatesQueryInterface */
    private $getLatestProductAxesRatesQuery;

    public function __construct(
        GetLatestProductAxesRatesQueryInterface $getLatestProductAxesRatesQuery,
        GetProductIdsFromProductIdentifiersQueryInterface $getProductIdsFromProductIdentifiersQuery
    ) {
        $this->getProductIdsFromProductIdentifiersQuery = $getProductIdsFromProductIdentifiersQuery;
        $this->getLatestProductAxesRatesQuery = $getLatestProductAxesRatesQuery;
    }

    /**
     * @inheritDoc
     */
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        $productIds = $this->getProductIdsFromProductIdentifiersQuery->execute($productIdentifiers);
        $productRates = $this->getLatestProductAxesRatesQuery->byProductIds($productIds);

        $additionalProperties = [];
        foreach ($productIds as $productIdentifier => $productId) {
            $productId = $productId->toInt();
            if (isset($productRates[$productId])) {
                $additionalProperties[$productIdentifier] = ['rates' => $productRates[$productId]->getRanks()];
            }
        }

        return $additionalProperties;
    }
}
