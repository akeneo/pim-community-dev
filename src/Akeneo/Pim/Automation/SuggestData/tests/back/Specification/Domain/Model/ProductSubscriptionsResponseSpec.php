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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class ProductSubscriptionsResponseSpec
{
    public function it_is_a_product_subscriptions_response()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(ProductSubscriptionsResponse::class);
    }

    public function it_contains_a_product_subscription_response_collection(
        ProductSubscriptionResponse $response1,
        ProductSubscriptionResponse $response2
    ) {
        $this->beConstructedWith([$response1, $response2]);

        $response1->getSubscriptionId()->willReturn('sub-1');
        $response1->getAttributes()->willReturn([]);

        $response2->getSubscriptionId()->willReturn('sub-2');
        $response2->getAttributes()->willReturn([]);

        $this->count()->shouldReturn(2);
    }
}
