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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ProductSubscribedSpec extends ObjectBehavior
{
    public function let(ProductSubscription $productSubscription): void
    {
        $this->beConstructedWith($productSubscription);
    }

    public function it_is_an_event(): void
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_is_a_product_subscribed_event(): void
    {
        $this->shouldBeAnInstanceOf(ProductSubscribed::class);
    }

    public function it_returns_the_product_subscription($productSubscription): void
    {
        $this->getProductSubscription()->shouldReturn($productSubscription);
    }
}
