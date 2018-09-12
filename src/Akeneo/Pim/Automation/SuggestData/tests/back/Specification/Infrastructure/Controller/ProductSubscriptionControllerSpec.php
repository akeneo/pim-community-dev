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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\ProductSubscriptionController;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionControllerSpec extends ObjectBehavior
{
    public function let(
        SubscribeProduct $subscribeProduct,
        GetProductSubscriptionStatusHandler $getProductSubscriptionStatusHandler,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ) {
        $this->beConstructedWith(
            $subscribeProduct,
            $getProductSubscriptionStatusHandler,
            $unsubscribeProductHandler
        );
    }

    public function it_is_a_product_subscription_controller()
    {
        $this->shouldBeAnInstanceOf(ProductSubscriptionController::class);
    }

    public function it_calls_unsubscribe_handler($unsubscribeProductHandler)
    {
        $productId = 42;

        $command = new UnsubscribeProductCommand($productId);
        $unsubscribeProductHandler->handle($command)->shouldBeCalled();

        $this->unsubscribeAction(42)->shouldReturnAnInstanceOf(JsonResponse::class);
    }
}
