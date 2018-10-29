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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi as InternalApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionController
{
    /** @var SubscribeProduct */
    private $subscribeProduct;

    /** @var GetProductSubscriptionStatusHandler */
    private $getProductSubscriptionStatusHandler;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /** @var InternalApi\ProductSubscriptionStatusNormalizer */
    private $productSubscriptionStatusNormalizer;

    /**
     * @param SubscribeProduct $subscribeProduct
     * @param GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer
     */
    public function __construct(
        SubscribeProduct $subscribeProduct,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer
    ) {
        $this->subscribeProduct = $subscribeProduct;
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->productSubscriptionStatusNormalizer = $productSubscriptionStatusNormalizer;
    }

    /**
     * @param int $productId
     *
     * @return Response
     */
    public function subscribeAction(int $productId): Response
    {
        try {
            $this->subscribeProduct->subscribe($productId);

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
