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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscriptionProcessor implements ItemProcessorInterface
{
    /** @var GetProductSubscriptionStatusHandler */
    private $getProductSubscriptionStatusHandler;

    /** @var SelectProductInfosForSubscriptionQueryInterface */
    private $selectProductInfosForSubscriptionQuery;

    public function __construct(
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        SelectProductInfosForSubscriptionQueryInterface $selectProductInfosForSubscriptionQuery
    ) {
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->selectProductInfosForSubscriptionQuery = $selectProductInfosForSubscriptionQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product): ProductSubscriptionRequest
    {
        $productId = new ProductId($product->getId());
        $productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle(
            new GetProductSubscriptionStatusQuery($productId)
        );

        try {
            $productSubscriptionStatus->validate();
        } catch (ProductSubscriptionException $exception) {
            throw new InvalidItemException(
                $exception->getMessage(),
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        $productInfos = $this->selectProductInfosForSubscriptionQuery->execute($productId);

        return new ProductSubscriptionRequest(
            $productInfos->getProductId(),
            $productInfos->getFamily(),
            $productInfos->getProductIdentifierValues(),
            $productInfos->getIdentifier()
        );
    }
}
