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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Query\GetProductSubscriptionStatusQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusQuerySpec extends ObjectBehavior
{
    private $productId;

    public function let(): void
    {
        $this->productId = 42;

        $this->beConstructedWith($this->productId);
    }

    public function it_is_a_product_subscription_status_query(): void
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionStatusQuery::class);
    }

    public function it_returns_the_product_id(): void
    {
        $this->getProductId()->shouldReturn(42);
    }
}
