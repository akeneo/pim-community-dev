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
