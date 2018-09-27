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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\SubscribeProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeProductSpec extends ObjectBehavior
{
    public function let(SubscribeProductHandler $handler): void
    {
        $this->beConstructedWith($handler);
    }

    public function it_is_a_subscribe_single_product_service(): void
    {
        $this->shouldHaveType(SubscribeProduct::class);
    }

    public function it_subscribes_a_product($handler): void
    {
        $handler->handle(Argument::type(SubscribeProductCommand::class))->shouldBeCalled();
        $this->subscribe(42);
    }
}
