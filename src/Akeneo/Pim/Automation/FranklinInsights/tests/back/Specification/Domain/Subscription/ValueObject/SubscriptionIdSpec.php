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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SubscriptionIdSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('subscription-id');
    }

    public function it_is_a_subscription_id(): void
    {
        $this->shouldHaveType(SubscriptionId::class);
    }

    public function it_returns_a_subscription_id(): void
    {
        $this->subscriptionId()->shouldReturn('subscription-id');
    }
}
