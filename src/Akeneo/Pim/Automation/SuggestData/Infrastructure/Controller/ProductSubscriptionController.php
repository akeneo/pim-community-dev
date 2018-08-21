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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Domain\Query\GetSubscriptionStatusForProductInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionController
{
    /** @var SubscribeProduct */
    private $subscribeProduct;

    /** @var GetSubscriptionStatusForProductInterface */
    private $getSubscriptionStatusForProduct;

    /**
     * @param SubscribeProduct $subscribeProduct
     * @param GetSubscriptionStatusForProductInterface $getSubscriptionStatusForProduct
     */
    public function __construct(
        SubscribeProduct $subscribeProduct,
        GetSubscriptionStatusForProductInterface $getSubscriptionStatusForProduct
    ) {
        $this->subscribeProduct = $subscribeProduct;
        $this->getSubscriptionStatusForProduct = $getSubscriptionStatusForProduct;
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
    public function isProductSubscribedAction(int $productId): Response
    {
        $isSubscribed = $this->getSubscriptionStatusForProduct->query($productId);

        return new JsonResponse($isSubscribed);
    }
}
