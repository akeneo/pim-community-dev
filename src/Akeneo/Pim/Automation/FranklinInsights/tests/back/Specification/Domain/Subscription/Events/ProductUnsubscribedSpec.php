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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductUnsubscribed;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ProductUnsubscribedSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(new ProductId(42));
    }

    public function it_is_an_event(): void
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_is_a_product_unsubscribed_event(): void
    {
        $this->shouldBeAnInstanceOf(ProductUnsubscribed::class);
    }

    public function it_returns_the_unsubscribed_product(): void
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId);
        $this->getUnsubscribedProductId()->shouldReturn($productId);
    }
}
