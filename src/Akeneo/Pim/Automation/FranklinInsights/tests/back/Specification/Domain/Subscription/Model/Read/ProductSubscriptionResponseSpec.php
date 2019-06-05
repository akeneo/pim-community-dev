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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionResponseSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_response(): void
    {
        $this->beConstructedWith(new ProductId(42), new SubscriptionId('subscription-id'), [], false, false);
        $this->shouldHaveType(ProductSubscriptionResponse::class);
    }

    public function it_exposes_the_product_id(): void
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId, new SubscriptionId('some-subscription-id'), [], false, false);
        $this->getProductId()->shouldReturn($productId);
    }

    public function it_exposes_the_subscription_id(): void
    {
        $subscriptionId = new SubscriptionId('a-random-id');

        $this->beConstructedWith(new ProductId(42), $subscriptionId, [], false, false);
        $this->getSubscriptionId()->shouldReturn($subscriptionId);
    }

    public function it_exposes_the_cancellation_status(): void
    {
        $this->beConstructedWith(new ProductId(42), new SubscriptionId('a-random-id'), [], false, true);
        $this->isCancelled()->shouldReturn(true);
    }

    public function it_exposes_the_suggested_data(): void
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->beConstructedWith(new ProductId(42), new SubscriptionId('subscription-id'), $suggestedData, false, false);
        $this->getSuggestedData()->shouldReturn($suggestedData);
    }

    public function it_returns_missing_mapping_information(): void
    {
        $this->beConstructedWith(new ProductId(42), new SubscriptionId('a-random-id'), [], false, false);
        $this->isMappingMissing()->shouldReturn(false);
    }
}
