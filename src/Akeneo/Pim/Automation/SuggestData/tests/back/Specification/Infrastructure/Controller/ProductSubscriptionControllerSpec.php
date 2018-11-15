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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ProductSubscriptionStatus;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\ProductSubscriptionController;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer as InternalApi;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function it_calls_unsubscribe_handler($unsubscribeProductHandler): void
    {
        $productId = 42;

        $command = new UnsubscribeProductCommand($productId);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();

        $this->unsubscribeAction(42)->shouldReturnAnInstanceOf(JsonResponse::class);
    }

    public function it_gets_a_product_subscription_status(
        $getProductSubscriptionStatusHandler,
        $productSubscriptionStatusNormalizer
    ): void {
        $connectionStatus = new ConnectionStatus(true, true);
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
