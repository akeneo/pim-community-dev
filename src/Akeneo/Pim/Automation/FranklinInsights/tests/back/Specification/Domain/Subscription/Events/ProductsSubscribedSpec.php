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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductsSubscribed;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ProductsSubscribedSpec extends ObjectBehavior
{
    public function let(ProductInterface $subscribedProductA, ProductInterface $subscribedProductB): void
    {
        $this->beConstructedWith([$subscribedProductA, $subscribedProductB]);
    }

    public function it_is_an_event(): void
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_is_a_products_subscribed_event(): void
    {
        $this->shouldBeAnInstanceOf(ProductsSubscribed::class);
    }

    public function it_returns_subscribed_products($subscribedProductA, $subscribedProductB): void
    {
        $this->getSubscribedProducts()->shouldReturn([$subscribedProductA, $subscribedProductB]);
    }
}
