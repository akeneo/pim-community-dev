<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;

/**
 * Handles a GetProductSubscriptionStatus query and returns a ProductSubscriptionStatus read model.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandler
{
    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    /** @var SelectProductInfosForSubscriptionQueryInterface */
    private $selectProductInfosForSubscriptionQuery;

    public function __construct(
        GetConnectionStatusHandler $getConnectionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ) {
        $this->getConnectionStatusHandler = $getConnectionStatusHandler;
        $this->selectProductInfosForSubscriptionQuery = $selectProductInfosForSubscriptionQuery;
    }

    /**
     * @param GetProductSubscriptionStatusQuery $query
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductSubscriptionStatus
     */
    public function handle(GetProductSubscriptionStatusQuery $query): ProductSubscriptionStatus
    {
        $productId = $query->getProductId();
        $productInfos = $this->selectProductInfosForSubscriptionQuery->execute($productId);

        if (!$productInfos instanceof ProductInfosForSubscription) {
            throw new \InvalidArgumentException(sprintf('There is no product with id "%s"', $productId->toInt()));
        }

        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return new ProductSubscriptionStatus(
            $connectionStatus,
            $productInfos->isSubscribed(),
            $productInfos->hasFamily(),
            $productInfos->getProductIdentifierValues()->hasAtLeastOneValue(),
            $productInfos->isVariant()
        );
    }
}
