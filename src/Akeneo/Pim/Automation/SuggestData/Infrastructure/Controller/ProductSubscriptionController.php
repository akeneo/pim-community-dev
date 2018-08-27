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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatus;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
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

    /**
     * @param SubscribeProduct $subscribeProduct
     * @param GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
     */
    public function __construct(
        SubscribeProduct $subscribeProduct,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler
    ) {
        $this->subscribeProduct = $subscribeProduct;
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
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
        } catch (\Exception $e) {
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
        $getProductSubscriptionStatus = new GetProductSubscriptionStatus($productId);
        $productSubscriptionStatus = $this->getProductSubscriptionStatusHandler->handle($getProductSubscriptionStatus);

        return new JsonResponse($productSubscriptionStatus->normalize());
    }
}
