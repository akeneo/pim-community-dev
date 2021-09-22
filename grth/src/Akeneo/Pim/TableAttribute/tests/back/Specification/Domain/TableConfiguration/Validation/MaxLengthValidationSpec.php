<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\MaxLengthValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\TableValidation;
use PhpSpec\ObjectBehavior;

class MaxLengthValidationSpec extends ObjectBehavior
{
    function it_can_be_created_with_an_integer()
    {
        $this->beConstructedThrough('fromValue', [1]);

        $this->shouldImplement(TableValidation::class);
        $this->shouldBeAnInstanceOf(MaxLengthValidation::class);
        $this->getValue()->shouldReturn(1);
    }

    function it_cannot_be_created_with_a_float()
    {
        $this->beConstructedThrough('fromValue', [1.5]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_with_a_negative_value()
    {
        $this->beConstructedThrough('fromValue', [-1]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_with_zero()
    {
        $this->beConstructedThrough('fromValue', [0]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_with_a_string()
    {
        $this->beConstructedThrough('fromValue', ['test']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
