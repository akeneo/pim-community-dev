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

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Query\GetProductSubscriptionStatus;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetProductSubscriptionStatusSpec extends ObjectBehavior
{
    private $productId;

    function let()
    {
        $this->productId = 42;

        $this->beConstructedWith($this->productId);
    }

    function it_is_a_product_subscription_status_query()
    {
        $this->shouldBeAnInstanceOf(GetProductSubscriptionStatus::class);
    }

    function it_returns_the_product_id()
    {
        $this->getProductId()->shouldReturn(42);
    }
}
