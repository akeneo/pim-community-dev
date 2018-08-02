<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ProductSubscriptionController
{
    /** @var SubscribeProduct */
    private $subscribeProduct;

    /**
     * @param SubscribeProduct $subscribeProduct
     */
    public function __construct(SubscribeProduct $subscribeProduct)
    {
        $this->subscribeProduct = $subscribeProduct;
    }

    /**
     * @param int $productId
     * @return JsonResponse
     */
    public function subscribeAction(int $productId)
    {
        try {
            $this->subscribeProduct->subscribe($productId);

            return new JsonResponse();
        } catch (\Exception $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
