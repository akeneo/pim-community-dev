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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer as InternalApi;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionController
{
    use CheckAccessTrait;

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
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        SubscribeProductHandler $subscribeProductHandler,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer,
        SecurityFacade $securityFacade
    ) {
        $this->subscribeProductHandler = $subscribeProductHandler;
        $this->getProductSubscriptionStatusHandler = $getProductSubscriptionStatusHandler;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->productSubscriptionStatusNormalizer = $productSubscriptionStatusNormalizer;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param Request $request
     * @param int $productId
     *
     * @return Response
     */
    public function subscribeAction(Request $request, int $productId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $this->checkAccess('akeneo_franklin_insights_product_subscription');

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
     * @param Request $request
     * @param int $productId
     *
     * @return Response
     */
    public function unsubscribeAction(Request $request, int $productId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $this->checkAccess('akeneo_franklin_insights_product_subscription');

        try {
            $command = new UnsubscribeProductCommand($productId);
            $this->unsubscribeProductHandler->handle($command);

            return new JsonResponse();
        } catch (ProductNotSubscribedException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
