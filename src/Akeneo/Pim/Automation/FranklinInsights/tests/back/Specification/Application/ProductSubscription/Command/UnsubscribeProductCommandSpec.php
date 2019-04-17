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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class UnsubscribeProductCommandSpec extends ObjectBehavior
{
    public function it_is_an_unsubscribe_product_command(): void
    {
        $this->beConstructedWith(new ProductId(42));
        $this->shouldHaveType(UnsubscribeProductCommand::class);
    }

    public function it_exposes_product_id(): void
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId);

        $this->getProductId()->shouldReturn($productId);
    }
}
