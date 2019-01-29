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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Controller\ProductSubscriptionController;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer as InternalApi;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionControllerSpec extends ObjectBehavior
{
    public function let(
        SubscribeProductHandler $subscribeProduct,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        InternalApi\ProductSubscriptionStatusNormalizer $productSubscriptionStatusNormalizer
    ): void {
        $this->beConstructedWith(
            $subscribeProduct,
            $getProductSubscriptionStatusHandler,
            $unsubscribeProductHandler,
            $productSubscriptionStatusNormalizer
        );
    }

    public function it_is_a_product_subscription_controller(): void
    {
        $this->shouldBeAnInstanceOf(ProductSubscriptionController::class);
    }

    public function it_calls_unsubscribe_handler($unsubscribeProductHandler, Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(true);

        $productId = 42;

        $command = new UnsubscribeProductCommand($productId);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();

        $this->unsubscribeAction($request, 42)->shouldReturnAnInstanceOf(JsonResponse::class);
    }

    public function it_redirects_to_home_during_subscription_if_request_is_not_xml_http(Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(false);
        $response = $this->subscribeAction($request, 42);

        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/');
    }

    public function it_redirects_to_home_during_unsubscription_if_request_is_not_xml_http(Request $request): void
    {
        $request->isXmlHttpRequest()->willReturn(false);
        $response = $this->unsubscribeAction($request, 42);

        $response->shouldBeAnInstanceOf(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/');
    }

    public function it_gets_a_product_subscription_status(
        $getProductSubscriptionStatusHandler,
        $productSubscriptionStatusNormalizer
    ): void {
        $connectionStatus = new ConnectionStatus(true, true, true, 0);
        $productSubscriptionStatus = new ProductSubscriptionStatus(
            $connectionStatus,
            true,
            true,
            true,
            false
        );
        $getProductSubscriptionStatusHandler
            ->handle(Argument::type(GetProductSubscriptionStatusQuery::class))
            ->willReturn($productSubscriptionStatus);

        $productSubscriptionStatusNormalizer->normalize($productSubscriptionStatus)->willReturn([
            'isConnectionActive' => true,
            'isIdentifiersMappingValid' => true,
            'isSubscribed' => true,
            'hasFamily' => true,
            'isMappingFilled' => true,
            'isProductVariant' => false,
        ]);

        $this->getProductSubscriptionStatusAction(42)->shouldReturnAnInstanceOf(JsonResponse::class);
    }
}
