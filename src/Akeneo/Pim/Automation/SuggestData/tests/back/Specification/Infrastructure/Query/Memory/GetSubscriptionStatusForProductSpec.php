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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Query\GetSubscriptionStatusForProductInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Memory\GetSubscriptionStatusForProduct;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSubscriptionStatusForProductSpec extends ObjectBehavior
{
    function let(InMemoryProductSubscriptionRepository $productSubscriptionRepository)
    {
        $this->beConstructedWith($productSubscriptionRepository);
    }

    function it_is_a_product_subscription_status_query()
    {
        $this->shouldImplement(GetSubscriptionStatusForProductInterface::class);
    }

    function it_is_a_fake_implementation_of_the_product_subscription_status_query()
    {
        $this->shouldBeAnInstanceOf(GetSubscriptionStatusForProduct::class);
    }

    function it_query_the_subscription_status_of_a_product($productSubscriptionRepository)
    {
        $subscription = new ProductSubscription(
            new Product(),
            'fake-subscription-id-to-franklin',
            []
        );
        $productSubscriptionRepository->getWrappedObject()->subscriptions[42] = $subscription;

        $this->query(42)->shouldReturn(true);
        $this->query(666)->shouldReturn(false);
    }
}
