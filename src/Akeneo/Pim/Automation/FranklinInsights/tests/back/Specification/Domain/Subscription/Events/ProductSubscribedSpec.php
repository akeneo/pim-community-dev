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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ProductSubscribedSpec extends ObjectBehavior
{
    public function let(ProductInterface $subscribedProduct): void
    {
        $this->beConstructedWith($subscribedProduct);
    }

    public function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_is_a_product_subscribed_event()
    {
        $this->shouldBeAnInstanceOf(ProductSubscribed::class);
    }

    public function it_returns_the_subscribed_product($subscribedProduct)
    {
        $this->getSubscribedProduct()->shouldReturn($subscribedProduct);
    }
}
