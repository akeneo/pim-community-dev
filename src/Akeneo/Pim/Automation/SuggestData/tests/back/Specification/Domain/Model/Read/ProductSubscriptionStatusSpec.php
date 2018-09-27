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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ProductSubscriptionStatus;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionStatusSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(true);
    }

    public function it_is_a_product_subscription_status(): void
    {
        $this->beAnInstanceOf(ProductSubscriptionStatus::class);
    }

    public function it_normalizes_itself(): void
    {
        $this->normalize()->shouldReturn(['is_subscribed' => true]);
    }
}
