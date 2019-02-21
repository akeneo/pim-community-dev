<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Datagrid;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

class FetchFranklinSubscriptionForProductRows implements AddAdditionalProductProperties
{
    /** @var ProductSubscriptionsExistQueryInterface */
    private $productSubscriptionsExistQuery;

    public function __construct(ProductSubscriptionsExistQueryInterface $productSubscriptionsExistQuery)
    {
        $this->productSubscriptionsExistQuery = $productSubscriptionsExistQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = $row->technicalId();
        }

        $productSubscriptions = $this->productSubscriptionsExistQuery->execute($productIds);

        $rowsWithAdditionalProperty = [];
        foreach ($rows as $row) {
            $isSubscribedToFranklin = (true === $productSubscriptions[$row->technicalId()] ? 'Enabled' : 'Disabled');
            $property = new AdditionalProperty('franklin_subscription', $isSubscribedToFranklin);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }
}
