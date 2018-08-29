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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatusHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusHandlerSpec extends ObjectBehavior
{
    function let(ProductSubscriptionRepositoryInterface $productSubscriptionRepository)
    {
        $this->beConstructedWith($productSubscriptionRepository);
    }

    function it_is_a_product_subscription_query_handler()
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionStatusHandler::class);
    }

    function it_returns_a_product_subscription_status_for_a_subscribed_product(
        $productSubscriptionRepository,
        ProductSubscription $productSubscription
    ) {
        $query = new GetProductSubscriptionStatusQuery(42);

        $productSubscriptionRepository->findOneByProductId(42)->willReturn($productSubscription);

        $productSubscriptionStatus = $this->handle($query);
        $productSubscriptionStatus->shouldIndicateAnActiveSubscription();
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return [
            'indicateAnActiveSubscription' => function ($productSubscriptionStatus) {
                return ['is_subscribed' => true] === $productSubscriptionStatus->normalize();
            },
        ];
    }
}
