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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer as InternalApi;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionController
{
    /** @var SubscribeProductHandler */
    private $subscribeProductHandler;

    /** @var GetProductSubscriptionStatusHandler */
    private $getProductSubscriptionStatusHandler;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /** @var InternalApi\ProductSubscriptionStatusNormalizer */
    private $productSubscriptionStatusNormalizer;

    /**
     * @param SubscribeProductHandler $subscribeProductHandler
     * @param GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer
     */
    public function __construct(
        SubscribeProductHandler $subscribeProductHandler,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer
    ) {
        $this->subscribeProductHandler = $subscribeProductHandler;
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->productSubscriptionStatusNormalizer = $productSubscriptionStatusNormalizer;
    }

    /**
     * @param int $productId
     *
     * @AclAncestor("akeneo_franklin_insights_product_subscription")
     *
     * @return Response
     */
    public function subscribeAction(int $productId): Response
    {
        try {
            $command = new SubscribeProductCommand($productId);
            $this->subscribeProductHandler->handle($command);

            return new JsonResponse();
        } catch (ProductSubscriptionException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param int $productId
     *
     * @return Response
     */
    public function getProductSubscriptionStatusAction(int $productId): Response
    {
        $getProductSubscriptionStatus = new GetProductSubscriptionStatusQuery($productId);
        $productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle($getProductSubscriptionStatus);

        return new JsonResponse($this->productSubscriptionStatusNormalizer->normalize($productSubscriptionStatus));
    }

    /**
     * @param int $productId
     *
     * @AclAncestor("akeneo_franklin_insights_product_subscription")
     *
     * @return Response
     */
    public function unsubscribeAction(int $productId): Response
    {
        try {
            $command = new UnsubscribeProductCommand($productId);
            $this->unsubscribeProductHandler->handle($command);

            return new JsonResponse();
        } catch (ProductSubscriptionException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
