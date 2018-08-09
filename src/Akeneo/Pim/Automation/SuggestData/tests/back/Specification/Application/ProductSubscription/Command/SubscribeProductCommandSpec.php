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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use PhpSpec\ObjectBehavior;

class SubscribeProductCommandSpec extends ObjectBehavior
{
    public function it_is_a_subscribe_product_command()
    {
        $this->beConstructedWith(42);
        $this->shouldHaveType(SubscribeProductCommand::class);
    }

    public function it_exposes_product_id()
    {
        $this->beConstructedWith(42);

        $this->getProductId()->shouldReturn(42);
    }

    public function it_throws_an_exception_if_product_id_is_negative()
    {
        $this->beConstructedWith(-42);
        $this
            ->shouldThrow(new \InvalidArgumentException('Product id should be a positive integer'))
            ->duringInstantiation();
    }
}
