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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionResponseSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_response(): void
    {
        $this->beConstructedWith(42, 'subscription-id', [], false, false);
        $this->shouldHaveType(ProductSubscriptionResponse::class);
    }

    public function it_cannot_be_instantiated_with_an_empty_subscription_id(): void
    {
        $this->beConstructedWith(42, '', [], false, false);
        $this->shouldThrow(new \InvalidArgumentException('subscription id cannot be empty'))->duringInstantiation();
    }

    public function it_exposes_the_product_id(): void
    {
        $this->beConstructedWith(42, 'some-subscription-id', [], false, false);
        $this->getProductId()->shouldReturn(42);
    }

    public function it_exposes_the_subscription_id(): void
    {
        $this->beConstructedWith(42, 'a-random-id', [], false, false);
        $this->getSubscriptionId()->shouldReturn('a-random-id');
    }

    public function it_exposes_the_cancellation_status(): void
    {
        $this->beConstructedWith(42, 'a-random-id', [], false, true);
        $this->isCancelled()->shouldReturn(true);
    }

    public function it_exposes_the_suggested_data(): void
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->beConstructedWith(42, 'subscription-id', $suggestedData, false, false);
        $this->getSuggestedData()->shouldReturn($suggestedData);
    }

    public function it_returns_missing_mapping_information(): void
    {
        $this->beConstructedWith(42, 'a-random-id', [], false, false);
        $this->isMappingMissing()->shouldReturn(false);
    }
}
