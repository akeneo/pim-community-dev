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
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
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

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product): ProductSubscriptionRequest
    {
        $productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle(
            new GetProductSubscriptionStatusQuery(new ProductId($product->getId()))
        );

        try {
            $productSubscriptionStatus->validate();
        } catch (ProductSubscriptionException $exception) {
            throw new InvalidItemException(
                $exception->getMessage(),
                new DataInvalidItem(['identifier' => $product->getIdentifier()])
            );
        }

        $fullProduct = $this->productRepository->find($product->getId());

        return new ProductSubscriptionRequest($fullProduct);
    }
}
