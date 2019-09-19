<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Indexing;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\ProductSubscriptionsExistQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetAdditionalPropertiesForProductProjectionInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductSubscriptionForProductProjection implements GetAdditionalPropertiesForProductProjectionInterface
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
    public function fromProductIdentifiers(array $productIdentifiers): array
    {
        if (empty($productIdentifiers)) {
            return [];
        }

        $productsSubscribedToFranklin = $this->productSubscriptionsExistQuery->executeWithIdentifiers(
            $productIdentifiers
        );

        return array_map(
            function (bool $isSubscribedToFranklin) {
                return ['franklin_subscription' => $isSubscribedToFranklin];
            },
            $productsSubscribedToFranklin
        );
    }
}
