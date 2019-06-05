<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class ProductIdSpec extends ObjectBehavior
{
    function it_is_a_product_id()
    {
        $this->beConstructedWith(42);
        $this->shouldHaveType(ProductId::class);
    }

    function it_returns_the_product_id_as_int()
    {
        $this->beConstructedWith(42);
        $this->toInt()->shouldReturn(42);
    }

    function it_throws_an_exception_if_the_id_is_zero()
    {
        $this->beConstructedWith(0);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_if_the_id_is_lower_than_zero()
    {
        $this->beConstructedWith(-1);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_determines_if_it_is_equal_to_another_product_id()
    {
        $this->beConstructedWith(42);
        $this->equals(new ProductId(42))->shouldReturn(true);
        $this->equals(new ProductId(41))->shouldReturn(false);
    }
}
