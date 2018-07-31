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

namespace spec\Akeneo\Pim\Automation\SuggestData\tests\back\spec\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Service\SubscribeSingleProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeSingleProductSpec extends ObjectBehavior
{
    function let(SubscribeProductHandler $handler)
    {
        $this->beConstructedWith($handler);
    }

    function it_is_a_subscribe_single_product_service()
    {
        $this->shouldHaveType(SubscribeSingleProduct::class);
    }

    function it_subscribes_a_product($handler)
    {
        $handler->handle(Argument::type(SubscribeProductCommand::class))->shouldBeCalled();
        $this->subscribe(42);
    }
}
